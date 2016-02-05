define([
    'underscore',
    'diamanteautomation/js/app/views/conditions/automation-conditions-collection-view',
    'tpl!diamanteautomation/js/app/templates/groupings/automation-groupings-edit-template.ejs',
    'oroui/js/app/views/base/view'
],function ( _,
             AutomationConditionsCollectionView,
             AutomationGroupingsEditTemplate,
             BaseView) {
    'use strict';

    var AutomationGroupingsEditView = BaseView.extend({
        autoRender: true,
        template : AutomationGroupingsEditTemplate,
        region: 'automation-conditions',
        className: 'control-group',

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

        getTemplateData: function() {
            var data = BaseView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        render: function () {
            var moreThanOne = this.model.get('children') ?
                this.model.get('children').length > 1 :
                this.model.get('conditions') && this.model.get('conditions').length;
            BaseView.prototype.render.apply(this, arguments);
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

        change: function (e) {
            var input = this.$(e.target);
            this.model.set( input.data('attr'), input.val(), { silent: true });
        },

        addCondition: function(e){
            e.preventDefault();
            this.conditions.collection.add({});
        },

        addGroup: function(){
            this.model.addGroup();
            delete this.conditions;
            this.renderSubViews();
        },

        removeGroup: function(){
            var success = this.model.destroy.bind(this.model);
            this.$el.animate({ opacity: 0 }, 500, success);
        }

    });

    return AutomationGroupingsEditView;
});