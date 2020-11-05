<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Venustheme
 * @package    Ves_Brand
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Brand\Model;

use Magento\Framework\DataObject\IdentityInterface;

/**
 * Brand Model
 */
class BrandsRepository extends \Magento\Framework\Model\AbstractModel implements \Ves\Brand\Api\BrandsRepositoryInterface
{


    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_brandHelper;

    /**
     * @param \Magento\Framework\Model\Context                          $context
     * @param \Magento\Framework\Registry                               $registry
     * @param \Ves\Brand\Model\ResourceModel\Brand|null                      $resource
     * @param \Ves\Brand\Model\ResourceModel\Brand\Collection|null           $resourceCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface                $storeManager
     * @param \Magento\Framework\UrlInterface                           $url
     * @param \Ves\Brand\Helper\Data                                    $brandHelper
     * @param array                                                     $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Ves\Brand\Model\ResourceModel\Brand $resource = null,
        \Ves\Brand\Model\ResourceModel\Brand\Collection $resourceCollection = null,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Ves\Brand\Helper\Data $brandHelper,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_brandHelper = $brandHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize customer model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ves\Brand\Model\ResourceModel\Brand');
    }

    public function get() {
            $brands = $this->_getResource()->getBrands();


        return $brands;
    }




}