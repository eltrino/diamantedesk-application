define([
    'underscore',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-edit-template.ejs',
    'oroui/js/app/views/base/view'
],function (_, AutomationActionsEditTemplate, BaseView) {
    'use strict';

    var AutomationActionsEditView = BaseView.extend({
        autoRender: true,
        template : AutomationActionsEditTemplate,
        region: 'automation-actions',

        listen: {
            'change model': 'render'
        },

        initialize: function(options){
            this.options = _.omit(options, 'model');
            console.log(options.model);
            this.delegate('change', ':input', this.change);
            return AutomationActionsEditView.__super__.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var data = AutomationActionsEditView.__super__.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        render: function () {
            AutomationActionsEditView.__super__.render.apply(this, arguments);
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

    return AutomationActionsEditView;
});