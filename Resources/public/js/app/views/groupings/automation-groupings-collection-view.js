define([
    'underscore',
    'diamanteautomation/js/app/views/groupings/automation-groupings-edit-view',
    'tpl!diamanteautomation/js/app/templates/groupings/automation-groupings-collection-template.ejs',
    'oroui/js/app/views/base/collection-view'
],function (_,
            AutomationGroupingsEditView,
            AutomationGroupingsCollectionTemplate,
            BaseCollectionView) {
    'use strict';

    var AutomationGroupingsCollectionView = BaseCollectionView.extend({
        autoRender: true,
        template : AutomationGroupingsCollectionTemplate,
        listSelector: '.grouping-children-list',

        listen: {
            'add collection': 'update',
            'remove collection': 'update',
            'reset collection': 'update'
        },

        initialize : function(options){
            this.options = _.omit(options, 'collection', 'container');
            this.options.hasParent = !!options.parent;
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            return new AutomationGroupingsEditView(_.extend({
                model: model,
                region: null,
                collectionView : AutomationGroupingsCollectionView
            }, this.options));
        },

        getTemplateData: function() {
            var data = BaseCollectionView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        removeGroup : function(){
            var success = this.collection.destroy.bind(this.collection);
            this.$el.animate({ opacity: 0 }, 500, success);
        },

        update : function(model){
            //var deleteButtons = this.$('> .conditions-list > div:not(".control-group") button[data-action="delete"]');
            //deleteButtons = deleteButtons.add(this.$(' > .conditions-list > .control-group > .conditions-buttons button[data-action="delete-group"]').parent());
            //deleteButtons.toggle(this.collection.length != 1);
        }

    });

    return AutomationGroupingsCollectionView;
});