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
            'click > .conditions-buttons button[data-action="add-group"]' : 'addGroup'
        },

        listen: {
            'add collection': 'update',
            'remove collection': 'update',
            'reset collection': 'update'
        },

        initialize : function(options){
            this.options = _.omit(options, 'collection');
            this.options.hasParent = !!options.collection.parent;
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            if(model.parent){
                return new AutomationConditionsCollectionView(_.extend(
                    this.options, {
                        collection: model,
                        region : null
                    }
                ));
            } else {
                return new AutomationConditionsEditView(_.extend(this.options, { model: model }));
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
            console.log(group);
            this.collection.add(group, this.options);
        },

        update : function(){
            this.$('> select[data-action="update-connector"]').toggle(this.collection.length != 1);
            this.$('button[data-action="delete"]').toggle(this.collection.length != 1);
        }

    });

    return AutomationConditionsCollectionView;
});