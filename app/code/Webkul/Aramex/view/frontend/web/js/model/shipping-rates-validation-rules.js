/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Aramex
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*global define*/
define(
    [],
    function () {
        'use strict';
        return {
            getRules: function () {
                return {
                    'country_id': {
                        'required': true
                    },
                    'postcode': {
                        'required': false
                    },
                    'city': {
                        'required': false
                    }
                };
            }
        };
    }
);
