define([
    'underscore',
    'diamanteautomation/js/app/views/conditions/automation-conditions-edit-view',
    'diamanteautomation/js/app/models/conditions/automation-conditions-collection',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-collection-template.ejs',
    'oroui/js/app/views/base/collection-view'
],function (_,
            AutomationConditionsEditView,
            AutomationConditionsCollection,
            AutomationConditionsCollectionTemplate,
            BaseCollectionView) {
    'use strict';

    var AutomationConditionsCollectionView = BaseCollectionView.extend({
        autoRender: true,
        template : AutomationConditionsCollectionTemplate,
        listSelector: '.conditions-list',
        className: 'control-group',

        listen: {
            'add collection': 'update',
            'remove collection': 'update',
            'reset collection': 'update'
        },

        initialize : function(options){
            this.options = _.omit(options, 'collection', 'container');
            this.options.hasParent = !!options.collection.parent;
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            return new AutomationConditionsEditView(_.extend({ model: model }, this.options));
        },

        getTemplateData: function() {
            var data = BaseCollectionView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        update : function(model){
            var deleteButtons = this.$('> .conditions-list > div:not(".control-group") button[data-action="delete"]');
            deleteButtons = deleteButtons.add(this.$(' > .conditions-list > .control-group > .conditions-buttons button[data-action="delete-group"]').parent());
            this.$('> select[data-action="update-connector"]').toggle(this.collection.length > 1);
            deleteButtons.toggle(this.collection.length != 1);
        }

    });

    return AutomationConditionsCollectionView;
});