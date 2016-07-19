define([
    'jquery',
    'underscore',
    'oroui/js/mediator',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-edit-template.ejs',
    //'oroui/js/app/components/base/component-container-mixin',
    'diamanteautomation/js/app/views/abstract/view',
    'chaplin'
],function ($, _, mediator, AutomationActionsEditTemplate, AbstractView, Chaplin) {
    'use strict';

    var AutomationActionsEditView = AbstractView.extend({
        template : AutomationActionsEditTemplate,

        listen: {
            'addedToParent' : 'onAdd',
            'change:entity model': 'entityChanged',
            'change model': 'render'
        },

        events: {
            'click button[data-action="delete"]': 'removeItem',
            'change :input': 'change'
        },

        initialize: function(options){
            this.options = _.omit(options, 'el', 'model');
            var entityTypeChanged = _.bind(this.entityTypeChanged, this);
            Chaplin.mediator.subscribe('automation/condition/entity-type', entityTypeChanged);
        },

        entityTypeChanged: function (type) {
            if (typeof this.model != 'undefined') {
                this.model.trigger('change', this.model, type);
            }
        },

        render: function () {
            this.conditionType = arguments[1];
            AbstractView.prototype.render.apply(this, arguments);
            this.$(':input:not(button)').trigger('change');
            if(this.model.collection.length == 1){
                this.$('button[data-action="delete"]').hide();
            }
            this.onAdd();
            this.initPageComponents();
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

        entityChanged: function(model, attr){
            this.options.target = attr;
            this.model.unset('property', {silent: true});
            this.model.unset('value', {silent: true});
        },

        removeItem: function(){
            var success = this.model.destroy.bind(this.model);
            this.model.unset('id');
            delete this.model.id;
            this.$el.animate({ opacity: 0 }, 500, success);
        },

        onAdd: function(){
            if(this.$el.is(':visible')){
                mediator.execute('layout:init', this.$el)
            }
        },

        getTemplateData: function() {
            var data = AbstractView.prototype.getTemplateData.call(this);

            data.conditionType = this.conditionType;

            return _.extend(data, this.options);
        }
    });

    return AutomationActionsEditView;
});