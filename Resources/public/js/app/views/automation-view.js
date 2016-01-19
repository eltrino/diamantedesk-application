define([
    'underscore',
    'diamanteautomation/js/app/models/automation-model',
    'tpl!diamanteautomation/js/app/templates/automation-template.ejs',
    'oroui/js/app/views/base/view'
],function (_, AutomationModel, AutomationTemplate, BaseView) {
    'use strict';

    var AutomationView = BaseView.extend({
        autoRender: true,

        regions: {
            'conditions': '#automation-conditions',
            'actions': '#automation-actions'
        },

        initialize: function(options){
            this.model = options.model ? new AutomationModel(options.model) : new AutomationModel({type: options.type });
            this.options = _.omit(options, 'el', 'model', 'type');
            this.template = AutomationTemplate;

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