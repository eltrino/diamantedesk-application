define([
    'underscore',
    'diamanteautomation/js/app/views/actions/automation-actions-edit-view',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-collection-template.ejs',
    'oroui/js/app/views/base/collection-view'
],function (_, AutomationActionsEditView, AutomationActionsCollectionTemplate, BaseCollectionView) {
    'use strict';

    var AutomationActionsCollection = BaseCollectionView.extend({
        autoRender: true,
        template : AutomationActionsCollectionTemplate,
        listSelector: '.actions-list',
        region: 'automation-actions',
        className: 'control-group',

        listen: {
            'add collection': 'update',
            'remove collection': 'update',
            'reset collection': 'update'
        },

        events: {
            'click button[data-action="add-item"]'  : 'addItem'
        },

        initialize : function(options){
            this.options = _.omit(options, 'collection');
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            return new AutomationActionsEditView(_.extend(this.options, { model: model }));
        },

        addItem : function(e){
            e.preventDefault();
            this.collection.add({});
        },

        update : function(){
            this.$('button[data-action="delete"]').toggle(this.collection.length != 1);
        }

    });

    return AutomationActionsCollection;
});