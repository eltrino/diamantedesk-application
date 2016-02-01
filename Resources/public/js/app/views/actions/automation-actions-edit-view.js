define([
    'jquery',
    'underscore',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-edit-template.ejs',
    'oroui/js/app/views/base/view'
],function ($, _, AutomationActionsEditTemplate, BaseView) {
    'use strict';

    var AutomationActionsEditView = BaseView.extend({
        autoRender: true,
        template : AutomationActionsEditTemplate,

        listen: {
            'change model': 'render'
        },

        events: {
            'click button[data-action="delete"]': 'removeItem',
            'change :input': 'change'
        },

        initialize: function(options){
            this.options = _.omit(options, 'model');
            BaseView.prototype.initialize.apply(this, arguments);
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this),
                def = _.clone(this.model.defaults);
            for(var key in data) {
                data[key] = data[key];
            }
            return _.extend(def, data, this.options);
        },

        render: function () {
            BaseView.prototype.render.apply(this, arguments);
            this.$(':input:not(button)').last().trigger('change');
            if(this.model.collection.length == 1){
                this.$('button[data-action="delete"]').hide();
            }
            return this;
        },

        change: function (e) {
            var input = $(e.target),
                model = this.model,
                relAttr = input.data('rel-attr');
            if(relAttr){
                _.each(relAttr.split(','), function(attr){
                    model.unset(attr, {silent: true});
                });
            }
            model.set( input.data('attr'), input.val() );
        },

        removeItem: function(){
            this.model.destroy();
        }

    });

    return AutomationActionsEditView;
});