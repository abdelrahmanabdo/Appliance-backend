<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ipragmatech\Ipwishlist\Api;

/**
 * @api
 * @since 100.0.2
 */
interface compareProductsInterface
{
    /**
     * Add product to Compare List
     *
     * @param int
     * @return string
     */
    public function addProduct($productId);

    /**
     * Retrieve Compare Items Collection
     *
     * @return array
     */
    public function getItemCollection();

}
