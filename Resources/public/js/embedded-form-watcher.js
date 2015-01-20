/*global define*/
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
