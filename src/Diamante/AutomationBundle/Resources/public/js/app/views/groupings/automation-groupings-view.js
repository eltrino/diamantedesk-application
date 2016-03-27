define([
    'underscore',
    'diamanteautomation/js/app/views/conditions/automation-conditions-collection-view',
    'tpl!diamanteautomation/js/app/templates/groupings/automation-groupings-template.ejs',
    'diamanteautomation/js/app/views/abstract/view'
],function ( _,
             AutomationConditionsCollectionView,
             AutomationGroupingsTemplate,
             AbstractView) {
    'use strict';

    var AutomationGroupingsView = AbstractView.extend({
        autoRender: true,
        template : AutomationGroupingsTemplate,
        region: 'automation-conditions',
        className: 'control-group',

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
            this.$('> .groupings-connector').toggle(moreThanOne);
            return this;
        },

        renderSubViews : function(){
            var container = this.$('> .grouping-list');
            var children = this.model.get('children');
            var conditions = this.model.get('conditions');
            if(children && children.length){
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

        addGroup: function(){
            this.model.addGroup();
            delete this.conditions;
            this.renderSubViews();
        }

    });

    return AutomationGroupingsView;
});