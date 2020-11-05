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
    'jquery',
    'uiComponent',
    'ko',
    'mage/url',
    'Magento_Checkout/js/model/quote',
    ],
    function ($, uiComponent, ko, urlBuilder, quote) {
        'use strict';

        function () {
            $.ajax({
                showLoader: true,
                url: urlBuilder.build('aramex/shipping/update'),
                data: { isAramexMethod : 1},
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                alert('Request Sent');
            });
        };

        return function (paymentMethod) {
            quote.paymentMethod(paymentMethod);
        }
    }
);