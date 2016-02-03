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
        },

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
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
            if(model.get(input.data('attr')) != input.val() && relAttr ){
                _.each(relAttr.split(','), function(attr){
                    model.unset(attr, {silent: true});
                });
            }
            model.set( input.data('attr'), input.val() );
        },

        removeItem: function(){
            var success = this.model.destroy.bind(this.model);
            this.$el.animate({ opacity: 0 }, 500, success);
        }

    });

    return AutomationActionsEditView;
});