/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Aramex
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        var aramexCod = window.checkoutConfig.webkularamex;
        if (aramexCod == 1) {
            rendererList.push(
                {
                    type: 'webkularamex',
                    component: 'Webkul_Aramex/js/view/payment/method-renderer/webkularamex'
                }
            );
        }

        return Component.extend({});
    }
);