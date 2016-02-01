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
        region: 'automation-conditions',
        className: 'control-group',

        events: {
            'click > .conditions-buttons button[data-action="add-item"]': 'addItem',
            'click > .conditions-buttons button[data-action="add-group"]' : 'addGroup',
            'click > .conditions-buttons button[data-action="delete-group"]' : 'removeGroup'
        },

        listen: {
            'add collection': 'update',
            'remove collection': 'update',
            'reset collection': 'update'
        },

        initialize : function(options){
            this.options = _.omit(options, 'collection', 'region');
            this.options.hasParent = !!options.collection.parent;
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            if(model.parent){
                return new AutomationConditionsCollectionView(_.extend({
                        collection: model,
                        region : null
                    },
                    this.options
                ));
            } else {
                return new AutomationConditionsEditView(_.extend({ model: model }, this.options));
            }
        },

        getTemplateData: function() {
            var data = BaseCollectionView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        addItem : function(e){
            e.preventDefault();
            this.collection.add({});
        },

        addGroup : function(e){
            var group = new AutomationConditionsCollection([{}], { parent : this.collection });
            this.collection.addSubCollection(group, this.options);
            this.subviewsByName['itemView:' + group.cid].subviews[0].delegateEvents();
        },

        removeGroup : function(){
            this.collection.destroy();
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