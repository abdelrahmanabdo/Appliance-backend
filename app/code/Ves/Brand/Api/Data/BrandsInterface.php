<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 100.0.2
 */
interface BrandsInterface
{

    /**
     * Get category name
     *
     * @return string
     */
    public function getName();



    /**
     * Get category string
     *
     * @return string
     */
    public function getImage();



}
