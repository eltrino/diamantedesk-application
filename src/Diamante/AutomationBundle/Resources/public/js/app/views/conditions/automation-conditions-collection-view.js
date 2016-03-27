define([
    'underscore',
    'diamanteautomation/js/app/views/conditions/automation-conditions-view',
    'diamanteautomation/js/app/views/conditions/automation-conditions-edit-view',
    'diamanteautomation/js/app/models/conditions/automation-conditions-collection',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-collection-template.ejs',
    'diamanteautomation/js/app/views/abstract/collection-view'
],function (_,
            AutomationConditionsView,
            AutomationConditionsEditView,
            AutomationConditionsCollection,
            AutomationConditionsCollectionTemplate,
            AbstractCollectionView) {
    'use strict';

    var AutomationConditionsCollectionView = AbstractCollectionView.extend({
        template : AutomationConditionsCollectionTemplate,
        itemView: AutomationConditionsView,
        editView: AutomationConditionsEditView,
        listSelector: '.conditions-list',
        className: 'control-group',

        initialize : function(options){
            this.options = _.omit(options, 'collection');
            this.options.hasParent = !!options.collection.parent;
            AbstractCollectionView.prototype.initialize.apply(this, arguments);
        },

        update : function(model){
            var deleteButtons = this.$('> .conditions-list > div:not(".control-group") button[data-action="delete"]');
            deleteButtons = deleteButtons.add(this.$(' > .conditions-list > .control-group > .conditions-buttons button[data-action="delete-group"]').parent());
            this.$('> select[data-action="update-connector"]').toggle(this.collection.length > 1);
            deleteButtons.toggle(this.collection.length != 1);
        },

        parentChanged : function(attr){
            this.options.target = attr;
            this.collection.invoke('set', {entity: attr });
        },

        add: function(){
            this.collection.add({}, this.options);
        }

    });

    return AutomationConditionsCollectionView;
});