define([
    'jquery',
    'underscore',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-template.ejs',
    'oroui/js/app/views/base/view'
],function ($, _, AutomationConditionsTemplate, BaseView) {
    'use strict';

    var AutomationConditionsView = BaseView.extend({
        autoRender: true,
        template : AutomationConditionsTemplate,

        initialize: function(options){
            this.options = _.omit(options, 'model');
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        }

    });

    return AutomationConditionsView;
});