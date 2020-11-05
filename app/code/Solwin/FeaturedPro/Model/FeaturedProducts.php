<?php

namespace Solwin\FeaturedPro\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ActionInterface;
use  \Magento\Catalog\Model\ProductRepository;
class FeaturedProducts  implements \Solwin\FeaturedPro\Api\featuredProductsInterface {

    /**
     * Product collection model
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_collection;


    private $productRepository;

    /**
     * Product collection model
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_helper;

    /**
     * Initialize
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Solwin\FeaturedPro\Helper\Data $helper
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param Collection $collection
     * @param array $data
     * @param  \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Solwin\FeaturedPro\Helper\Data $helper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Collection $collection,
        array $data = [],
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository

    ) {
        $this->_collection = $collection;
        $this->_helper = $helper;

        $this->productRepository = $productRepository;

        $this->pageConfig->getTitle()->set(__($this
            ->_helper
            ->getConfigValue(
                'featuredpro_settings/featured_products/title'
            )));


    }

    /**
     * Get products
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function get () {
        $collection = $this->_collection;

        $collection->addAttributeToSelect('*')->addAttributeToFilter('is_featured', 1, 'left')
        ;
        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');


        $collection->load();

        $collection->addCategoryIds();

        foreach ($collection->getData() as $data2) {
            return $data2;
        }
//        return $collection->getData();

//        return $searchResult;
//        $limit = $this->getData('widget_limit');
//
////        $this->_collection->clear()->getSelect()->reset('where');
//        $collection = $this->_collection->addAttributeToSelect('*')
//            ->addAttributeToFilter('is_featured', 1, 'left')
//            ->ad  dAttributeToFilter('is_saleable', 1, 'left')
//            ->getData();
//
//
//        return $collection;

    }


}
?>