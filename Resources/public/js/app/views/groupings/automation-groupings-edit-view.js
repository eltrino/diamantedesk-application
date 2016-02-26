define([
    'underscore',
    'diamanteautomation/js/app/views/conditions/automation-conditions-collection-view',
    'tpl!diamanteautomation/js/app/templates/groupings/automation-groupings-edit-template.ejs',
    'diamanteautomation/js/app/views/abstract/view'
],function ( _,
             AutomationConditionsCollectionView,
             AutomationGroupingsEditTemplate,
             AbstractView) {
    'use strict';

    var AutomationGroupingsEditView = AbstractView.extend({
        template : AutomationGroupingsEditTemplate,
        region: 'automation-conditions',
        className: 'control-group',

        listen: {
            'parent:change model' : 'parentChanged',
            'children:empty' : 'childrenEmptied'
        },

        events: {
            'change > .groupings-connector' : 'change',
            'click > .groupings-buttons button[data-action="add-item"]': 'addCondition',
            'click > .groupings-buttons button[data-action="add-group"]' : 'addGroup',
            'click > .groupings-buttons button[data-action="delete-group"]' : 'removeGroup'
        },

        initialize: function(options){
            this.collectionView = options.collectionView;
            this.options = _.omit(options, 'model', 'collection', 'collectionView');
            this.options.hasParent = !!options.parent;
            this.options.hasChildren = !!options.children;
        },

        render: function () {
            var moreThanOne = this.model.get('children') ?
                this.model.get('children').length > 1 :
                this.model.get('conditions') && this.model.get('conditions').length;
            AbstractView.prototype.render.apply(this, arguments);
            this.renderSubViews();
            this.$('> .groupings-connector').trigger('change');
            this.$('> .groupings-connector').toggle(moreThanOne);
            return this;
        },

        renderSubViews : function(){
            var container = this.$('> .grouping-list');
            var children = this.model.get('children');
            var conditions = this.model.get('conditions');
            if(children){
                this.children = new this.collectionView(_.extend({
                    collection: children,
                    container: container,
                    parent : this
                }, this.options));
            } else if(conditions) {
                this.conditions = new AutomationConditionsCollectionView(_.extend({
                    collection: conditions,
                    container: container
                }, this.options))
            }
        },

        parentChanged: function(attr){
            var children = this.model.get('children');
            var conditions = this.model.get('conditions');
            this.options.target = attr;
            if(children) {
                children.trigger('parent:change', attr);
            } else if (conditions){
                conditions.trigger('parent:change', attr);
            }
        },

        childrenEmptied: function(){
            delete this.children;
            this.model.removeGroups(this.options);
            this.$('> .groupings-buttons button[data-action="add-item"]').show();
            this.renderSubViews();
        },

        change: function (e) {
            var input = this.$(e.target);
            this.model.set( input.data('attr'), input.val(), { silent: true });
        },

        addCondition: function(e){
            e.preventDefault();
            this.conditions.add();
        },

        addGroup: function(e){
            e.preventDefault();
            this.model.addGroup(this.options);
            if(this.conditions){
                delete this.conditions;
                this.$('> .groupings-buttons button[data-action="add-item"]').hide();
                this.renderSubViews();
            }
        },

        removeGroup: function(){
            var success = this.model.destroy.bind(this.model);
            this.$el.animate({ opacity: 0 }, 500, success);
        }

    });

    return AutomationGroupingsEditView;
});