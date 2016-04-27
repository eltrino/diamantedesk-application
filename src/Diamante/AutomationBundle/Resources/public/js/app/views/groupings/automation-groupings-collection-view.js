define([
    'underscore',
    'diamanteautomation/js/app/views/groupings/automation-groupings-view',
    'diamanteautomation/js/app/views/groupings/automation-groupings-edit-view',
    'tpl!diamanteautomation/js/app/templates/groupings/automation-groupings-collection-template.ejs',
    'diamanteautomation/js/app/views/abstract/collection-view'
],function (_,
            AutomationGroupingsView,
            AutomationGroupingsEditView,
            AutomationGroupingsCollectionTemplate,
            AbstractCollectionView) {
    'use strict';

    var AutomationGroupingsCollectionView = AbstractCollectionView.extend({
        autoRender: true,
        template : AutomationGroupingsCollectionTemplate,
        listSelector: '.grouping-children-list',

        initialize : function(options){
            this.options = _.omit(options, 'collection', 'container');
            this.options.hasParent = !!options.parent;
            AbstractCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            if(this.options.edit){
                return new AutomationGroupingsEditView(_.extend({
                    model: model,
                    region: null,
                    collectionView : AutomationGroupingsCollectionView
                }, this.options));
            } else {
                return new AutomationGroupingsView(_.extend({
                    model: model,
                    region: null,
                    collectionView : AutomationGroupingsCollectionView
                }, this.options));
            }
        },

        update : function(model, collection){
            if(collection.length === 0){
                this.options.parent.trigger('children:empty');
            }
            //var deleteButtons = this.$('> .conditions-list > div:not(".control-group") button[data-action="delete"]');
            //deleteButtons = deleteButtons.add(this.$(' > .conditions-list > .control-group > .conditions-buttons button[data-action="delete-group"]').parent());
            //deleteButtons.toggle(this.collection.length != 1);
        },

        parentChanged : function(attr){
            this.options.target = attr;
            this.collection.each(function(model){
                var children = model.get('children');
                var conditions = model.get('conditions');
                if(children) {
                    children.trigger('parent:change', attr);
                } else if (conditions){
                    conditions.trigger('parent:change', attr);
                }
            });
        }

    });

    return AutomationGroupingsCollectionView;
});