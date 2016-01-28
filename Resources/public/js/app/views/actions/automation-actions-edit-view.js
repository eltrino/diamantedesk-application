define([
    'underscore',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-edit-template.ejs',
    'oroui/js/app/views/base/view'
],function (_, AutomationActionsEditTemplate, BaseView) {
    'use strict';

    var AutomationActionsEditView = BaseView.extend({
        autoRender: true,
        template : AutomationActionsEditTemplate,

        listen: {
            'change model': 'render'
        },

        events: {
            'click button[data-action="delete"]': 'removeItem'
        },

        initialize: function(options){
            this.options = _.omit(options, 'model');
            this.delegate('change', ':input', this.change);
            BaseView.prototype.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        render: function () {
            BaseView.prototype.render.apply(this, arguments);
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
        },

        removeItem: function(){
            this.model.destroy();
        }

    });

    return AutomationActionsEditView;
});