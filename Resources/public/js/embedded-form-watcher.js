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
    'jquery',
    'backbone'
], function ($, Backbone) {

    var $formTypeField;
    var $channelField;
    var $branchField;

    function processFormTypeChange() {
        if ($formTypeField.find("option:selected").val() == "diamante_embedded_form.form_type.available_embedded_form") {
            $branchField.parent().parent().removeClass('hide');
            $channelField.parent().parent().addClass('hide');
        } else {
            $branchField.parent().parent().addClass('hide');
            $channelField.parent().parent().removeClass('hide');
        }
    }

    return Backbone.View.extend({
        initialize: function (options) {
            $formTypeField = $('#' + options.formTypeFieldId);
            $branchField = $('#' + options.branchFieldId);
            $channelField = $('#' + options.channelFieldId);
        },
        startWatching: function () {
            $formTypeField.change(processFormTypeChange);
            $formTypeField.trigger('change');
        }
    });

});
