/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
define([
    'jquery'
], function ($) {
    'use strict';
    var switcher = $('#diamante_desk_channels_diamante_oro_crm_integration___create_contact_value');
    var targetContainer = $("[id*='diamante_desk_channels_diamante_oro_crm_integration___default_owner'].control-subgroup");

    $(switcher).change(function () {
        var val = !parseInt($(this).val());
        if (val) {
            $(targetContainer).find('.select2-search-choice-close').mousedown();
        }
        $(targetContainer).find('input,button').prop('disabled', val);
        $(this).focus();
    });

    $(document).ready(function(){
        var val = !parseInt(switcher.val());
        $(targetContainer).find('input,button').prop('disabled', val).blur();
        if (val) {
            $(targetContainer).find('.select2-search-choice-close').mousedown();
        }
    });

});