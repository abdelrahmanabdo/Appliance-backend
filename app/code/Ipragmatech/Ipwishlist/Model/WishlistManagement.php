<?php
/**
 * Contributor company: iPragmatech solution Pvt Ltd.
 * Contributor Author : Manish Kumar
 * Date: 23/5/16
 * Time: 11:55 AM
 */

namespace Ipragmatech\Ipwishlist\Model;

use Ipragmatech\Ipwishlist\Api\WishlistManagementInterface;
use Magento\Wishlist\Controller\WishlistProvider;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Defines the implementaiton class of the WishlistManagementInterface
 */
class WishlistManagement implements WishlistManagementInterface
{

    /**
     * @var CollectionFactory
     */
    protected $_wishlistCollectionFactory;

    /**
     * Wishlist item collection
     *
     * @var \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    protected $_itemCollection;

    /**
     * @var WishlistRepository
     */
    protected $_wishlistRepository;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;
    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;
    /**
     * @var Item
     */
    protected $_itemFactory;


    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        CollectionFactory $wishlistCollectionFactory,
        WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Wishlist\Model\ItemFactory $itemFactory
    ) {
        $this->_wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->_wishlistRepository = $wishlistRepository;
        $this->_productRepository = $productRepository;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_itemFactory = $itemFactory;
    }

    /**
     * Get wishlist collection
     * @deprecated
     * @param $customerId
     * @return WishlistData
     */
    public function getWishlistForCustomer($customerId)
    {
        // $url = 'https://fcm.googleapis.com/fcm/send';
        // //echo $url;
        // $ch = curl_init();
        // $auth_key = 'AAAAw5dMiHo:APA91bGhd7aFYH_f8yhrFM-dchh1VUQrUcbfyDPQ3rUw3fqJhNV5DeXP2D1ogOu-bWvaOWc4eeOMyiUEJay918-v0TgpPc_z2X31oAB7j00OLP-wG5jbqWjvrTxW_E2OOgM5UeUIU_2z'; //get from fcm.googleapis.com
        // $deviceToken='c4hFKb7FZsY:APA91bG7cadb5jX5fux28ORkycBA_Qos0ADf9MbopQHkc0It0OJRmvXCZE41xurkobA9dYv8n7iNWOUvDe6QUvtjfbyUb3M7HTBangh0Xbs7gJVAj2eyYxHdkU_WeynoGVNE12zUpV5h7Ra3yBNxsAP0aOS-FlnI9g'; // get for device
        // $message = array('title'=>'sai', 'body'=>'Hai');
     
        // $fields = array(
        //     'to' => $deviceToken,
        //     'data' => $message,
        // );
        // // print_r(json_encode($fields));
        // $headers = array(
        //     'Authorization: key=' .$auth_key,
        //     'Content-Type: application/json'
        // );
        // $ch = curl_init();
        // curl_setopt( $ch, CURLOPT_URL, $url);
        // curl_setopt( $ch, CURLOPT_POST, true );
        // curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($fields));
        // $response=curl_exec($ch);
        // print_r($response);

        // $response = json_decode($response,true);
        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new InputException(__('Id required'));
        } else {
            $collection =
                $this->_wishlistCollectionFactory->create()
                    ->addCustomerIdFilter($customerId);
            
            $wishlistData = [];
            foreach ($collection as $item) {
                $productInfo = $item->getProduct()->toArray();
                $data = [
                    "wishlist_item_id" => $item->getWishlistItemId(),
                    "wishlist_id"      => $item->getWishlistId(),
                    "product_id"       => $item->getProductId(),
                    "store_id"         => $item->getStoreId(),
                    "added_at"         => $item->getAddedAt(),
                    "description"      => $item->getDescription(),
                    "qty"              => round($item->getQty()),
                    "product"          => $productInfo
                ];
                $wishlistData[] = $data;
            }
            return $wishlistData;
        }
    }

    /**
     * Add wishlist item for the customer
     * @param int $customerId
     * @param int $productIdId
     * @return array|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addWishlistForCustomer($customerId, $productId)
    {
        if ($productId == null) {
            throw new LocalizedException(__
            ('Invalid product, Please select a valid product'));
        }
        try {
            $product = $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }
        try {
            $wishlist = $this->_wishlistRepository->create()->loadByCustomerId
            ($customerId, true);
            $wishlist->addNewItem($product);
            $returnData = $wishlist->save();
        } catch (NoSuchEntityException $e) {

        }
        return true;
    }

    /**
     * Delete wishlist item for customer
     * @param int $customerId
     * @param int $productIdId
     * @return bool|\Magento\Wishlist\Api\status
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteWishlistForCustomer($customerId, $wishlistItemId)
    {

        if ($wishlistItemId == null) {
            throw new LocalizedException(__
            ('Invalid wishlist item, Please select a valid item'));
        }
        $item = $this->_itemFactory->create()->load($wishlistItemId);
        if (!$item->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List Item doesn\'t exist.')
            );
        }
        $wishlistId = $item->getWishlistId();
        $wishlist = $this->_wishlistFactory->create();

        if ($wishlistId) {
            $wishlist->load($wishlistId);
        } elseif ($customerId) {
            $wishlist->loadByCustomerId($customerId, true);
        }
        if (!$wishlist) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
        if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The requested Wish List doesn\'t exist.')
            );
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (\Exception $e) {

        }
        return true;
    }

    /**
     * Return count of wishlist item for customer
     * @param int $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWishlistInfo($customerId){

        if (empty($customerId) || !isset($customerId) || $customerId == "") {
            throw new InputException(__('Id required'));
        } else {
            $collection =
                $this->_wishlistCollectionFactory->create()
                    ->addCustomerIdFilter($customerId);

            $totalItems = count($collection);

            $data = [
                "total_items"      => $totalItems
            ];

            $wishlistData[] = $data;

            return $wishlistData;
        }
    }
}