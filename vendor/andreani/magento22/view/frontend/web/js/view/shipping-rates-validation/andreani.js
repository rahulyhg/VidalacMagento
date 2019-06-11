/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Ids_Andreani/js/model/shipping-rates-validator',
    'Ids_Andreani/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    andreaniShippingRatesValidator,
    andreaniShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('andreaniestandar', andreaniShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('andreaniestandar', andreaniShippingRatesValidationRules);

    defaultShippingRatesValidator.registerValidator('andreanisucursal', andreaniShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('andreanisucursal', andreaniShippingRatesValidationRules);

    defaultShippingRatesValidator.registerValidator('andreaniurgente', andreaniShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('andreaniurgente', andreaniShippingRatesValidationRules);

    return Component;
});