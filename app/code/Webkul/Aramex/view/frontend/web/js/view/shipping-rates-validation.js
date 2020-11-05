/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Aramex
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        uspsShippingRatesValidator,
        uspsShippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('webkularamex', uspsShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('webkularamex', uspsShippingRatesValidationRules);
        return Component;
    }
);
