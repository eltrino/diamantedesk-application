/*global define*/
define([
    'jquery',
    'backbone'
], function ($, Backbone) {

    var $formTypeField;
    var $branchField;

    function isFormStateChanged(currentCss, currentSuccessMessage) {
        return !(currentCss === rememberedCss && currentSuccessMessage === rememberedSuccessMessage);
    }

    function processFormTypeChange() {
        if ($formTypeField.find("option:selected").val() == "diamante_embedded_form.form_type.available_embedded_form") {
            $branchField.parent().parent().removeClass('hide');
        } else {
            $branchField.parent().parent().addClass('hide');
        }
    }

    return Backbone.View.extend({
        initialize: function (options) {
            $formTypeField = $('#' + options.formTypeFieldId);
            $branchField = $('#' + options.branchFieldId);
        },
        startWatching: function () {
            $formTypeField.change(processFormTypeChange);
            $formTypeField.trigger('change');
        }
    });

});
