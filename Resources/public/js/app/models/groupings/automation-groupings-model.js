define([
    'underscore',
    'diamanteautomation/js/app/models/groupings/automation-groupings-collection',
    'diamanteautomation/js/app/models/conditions/automation-conditions-collection',
    'oroui/js/app/models/base/model'
],function (_, AutomationGroupingsCollection, AutomationConditionCollection, BaseModel) {
    'use strict';

    var AutomationGroupingsModel = BaseModel.extend({

        defaults: {
            connector: '',
            conditions: []
        },

        initialize: function(attr, options){
            if (attr.children && attr.children.length) {
                this.set('children', new AutomationGroupingsCollection(attr.children, { model: AutomationGroupingsModel }));
            } else if(attr.conditions) {
                this.set('conditions', new AutomationConditionCollection(attr.conditions));
            } else {
                this.set('conditions', new AutomationConditionCollection([{}]));
            }
        },

        validate: function(attrs, options) {

        },

        addGroup: function(collection) {
            var conditions = this.get('conditions');
            var children = new AutomationGroupingsCollection(
                [{ conditions: conditions ? conditions.serialize() : {} }],
                { model: AutomationGroupingsModel });
            if(conditions) {
                conditions.dispose();
                this.unset('conditions');
            }
            this.set('children', children);
        }
    });

    return AutomationGroupingsModel;
});