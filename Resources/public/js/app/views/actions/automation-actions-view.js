define([
    'jquery',
    'underscore',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-template.ejs',
    'oroui/js/app/views/base/view'
],function ($, _, AutomationActionsTemplate, BaseView) {
    'use strict';

    var AutomationActionsView = BaseView.extend({
        autoRender: true,
        template : AutomationActionsTemplate,

        initialize: function(options){
            this.options = _.omit(options, 'model');
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        }

    });

    return AutomationActionsView;
});