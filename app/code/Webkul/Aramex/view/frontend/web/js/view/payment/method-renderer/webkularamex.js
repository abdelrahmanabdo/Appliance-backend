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
        'Magento_Checkout/js/view/payment/default',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, priceUtils, quote) {
        'use strict';
        var aramexCod = window.checkoutConfig.webkularamex;
        return Component.extend({
            defaults: {
                template: 'Webkul_Aramex/payment/webkularamex'
            },

            /** Returns is method available */
            isAvailable: function () {
                if (quote.shippingMethod() !== null) {
                    return quote.shippingMethod().carrier_code === 'webkularamex' && aramexCod == 1;
                }

            },
            /** Returns payment method instructions */
            getInstructions: function () {
                if (aramexCod == 1) {
                    return window.checkoutConfig.instructions[this.item.method];
                }
            },
            getCodCharge: function () {
                return this.getValue();
            },
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },
            getValue: function () {
                return this.getFormattedPrice(window.checkoutConfig.aramexCodCharge);
            }
        });
    }
);