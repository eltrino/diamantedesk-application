define([
    'underscore',
    'diamanteautomation/js/app/views/actions/automation-actions-view',
    'diamanteautomation/js/app/views/actions/automation-actions-edit-view',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-collection-template.ejs',
    'diamanteautomation/js/app/views/abstract/collection-view'
],function (_,
            AutomationActionsView,
            AutomationActionsEditView,
            AutomationActionsCollectionTemplate,
            AbstractCollectionView) {
    'use strict';

    var AutomationActionsCollection = AbstractCollectionView.extend({
        template : AutomationActionsCollectionTemplate,
        itemView: AutomationActionsView,
        editView: AutomationActionsEditView,
        listSelector: '.actions-list',
        region: 'automation-actions',
        className: 'control-group',

        events: {
            'click button[data-action="add-item"]'  : 'addItem'
        },

        addItem : function(e){
            e.preventDefault();
            this.collection.add({}, this.options);
        },

        update : function(){
            this.$('button[data-action="delete"]').toggle(this.collection.length != 1);
        },

        parentChanged : function(attr){
            this.options.target = attr;
            this.collection.invoke('set', {entity: attr });
        }

    });

    return AutomationActionsCollection;
});