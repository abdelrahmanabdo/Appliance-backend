<?php

namespace Solwin\FeaturedPro\Api;


interface featuredProductsInterface
{


    /**
     * Get featured products list
     *
     * @api
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function get();


}
