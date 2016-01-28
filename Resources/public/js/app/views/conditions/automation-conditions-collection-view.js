define([
    'underscore',
    'diamanteautomation/js/app/views/conditions/automation-conditions-edit-view',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-collection-template.ejs',
    'oroui/js/app/views/base/collection-view'
],function (_, AutomationConditionsEditView, AutomationConditionsCollectionTemplate, BaseCollectionView) {
    'use strict';

    var AutomationConditionsCollection = BaseCollectionView.extend({
        autoRender: true,
        template : AutomationConditionsCollectionTemplate,
        listSelector: '.conditions-list',
        region: 'automation-conditions',

        events: {
            'click button[data-action="add"]': 'addItem'
        },

        initialize : function(options){
            this.options = _.omit(options, 'collection');
            BaseCollectionView.prototype.initialize.apply(this, arguments);
        },

        initItemView : function(model){
            return new AutomationConditionsEditView(_.extend(this.options, { model: model }));
        },

        getTemplateData: function() {
            var data = BaseCollectionView.prototype.getTemplateData.call(this);
            return _.extend(data, this.options);
        },

        addItem : function(e){
            e.preventDefault();
            this.collection.add({});
        }
    });

    return AutomationConditionsCollection;
});