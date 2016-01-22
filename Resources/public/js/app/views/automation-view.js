define([
    'underscore',
    'tpl!diamanteautomation/js/app/templates/automation-template.ejs',
    'oroui/js/app/views/base/view'
],function (_, AutomationTemplate, BaseView) {
    'use strict';

    var AutomationView = BaseView.extend({
        autoRender: true,
        template : AutomationTemplate,

        regions: {
            'automation-conditions': '#automation-conditions',
            'automation-actions': '#automation-actions'
        },

        initialize: function(options){
            this.options = _.omit(options, 'el', 'model');
            return AutomationView.__super__.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var data = AutomationView.__super__.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        render: function () {
            return AutomationView.__super__.render.apply(this, arguments);
        }
    });

    return AutomationView;
});