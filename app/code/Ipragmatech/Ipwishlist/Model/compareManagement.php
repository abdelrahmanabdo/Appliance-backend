<?php
/**
 * Contributor company: iPragmatech solution Pvt Ltd.
 * Contributor Author : Manish Kumar
 * Date: 23/5/16
 * Time: 11:55 AM
 */

namespace Ipragmatech\Ipwishlist\Model;

use Ipragmatech\Ipwishlist\Api\compareProductsInterface;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;

/**
 * Defines the implementaiton class of the WishlistManagementInterface
 */
class compareManagement extends \Magento\Framework\DataObject implements compareProductsInterface
{

    /**
     * Customer visitor
     *
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Catalog product compare item
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item
     */
    protected $_catalogProductCompareItem;

    /**
     * Item collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * Compare item factory
     *
     * @var \Magento\Catalog\Model\Product\Compare\ItemFactory
     */
    protected $_compareItemFactory;


    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $catalogProductCompareItem
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Catalog\Model\Product\Compare\ListCompare $listCompare,
     * @param array $data
     */


    public function __construct(
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $catalogProductCompareItem,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $listCompare,
        array $data = []
    ) {
        $this->listCompare = $listCompare;
        $this->_compareItemFactory = $compareItemFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_catalogProductCompareItem = $catalogProductCompareItem;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct($productId)
    {
        $productIds = [];
        array_push($productIds,$productId);

        if($this->listCompare->addProducts($productIds)) {
            return "true";
        }else {
            return "false";
        }

    }


    /**
     * {@inheritdoc}
     */
    public function getItemCollection()
    {

        $items =  $this->listCompare->getItemCollection();
        return $items;
    }

}