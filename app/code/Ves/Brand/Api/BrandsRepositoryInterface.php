<?php

namespace Ves\Brand\Api;

interface BrandsRepositoryInterface
{

    /**
     * Get all brands
     * @api
     * @return \Ves\Brand\Api\Data\BrandsInterface
     */
    public function get();
}