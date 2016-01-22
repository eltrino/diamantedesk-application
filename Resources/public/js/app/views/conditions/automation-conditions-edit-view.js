define([
    'underscore',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-edit-template.ejs',
    'oroui/js/app/views/base/view'
],function (_, AutomationConditionsEditTemplate, BaseView) {
    'use strict';

    var AutomationConditionsEditView = BaseView.extend({
        autoRender: true,
        template : AutomationConditionsEditTemplate,
        region: 'automation-conditions',

        listen: {
            'change model': 'render'
        },

        initialize: function(options){
            this.options = _.omit(options, 'model');
            console.log(options.model);
            this.delegate('change', ':input', this.change);
            return AutomationConditionsEditView.__super__.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var data = AutomationConditionsEditView.__super__.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        render: function () {
            AutomationConditionsEditView.__super__.render.apply(this, arguments);
            this.$(':input').trigger('change');
            return this;
        },

        nameToAttr: function(name){
            var result =  name.match(/actions\[(.+?)\]/);
            return result && result[1];
        },

        change: function (e) {
            var input = e.target;
            this.model.set(this.nameToAttr(input.name), input.value );
        }

    });

    return AutomationConditionsEditView;
});