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
            options = _.omit(options, 'el', 'model');
            if (attr.children && attr.children.length) {
                options.model = AutomationGroupingsModel;
                this.set('children', new AutomationGroupingsCollection(attr.children, options));
                this.unset('conditions');
            } else if(attr.conditions) {
                this.set('conditions', new AutomationConditionCollection(attr.conditions, options));
            } else {
                this.set('conditions', new AutomationConditionCollection([{}], options));
            }
        },

        validate: function(attrs, options) {

        },

        addGroup: function(options) {
            var conditions = this.get('conditions');
            var children = this.get('children');
            var child = new AutomationGroupingsCollection(
                [{ conditions: conditions ? conditions.serialize() : {} }],
                _.extend({ model: AutomationGroupingsModel }, options)
            );
            if(conditions) {
                conditions.dispose();
                this.unset('conditions');
            }
            if(children && children.length){
                children.add({}, options);
            } else {
                this.set('children', child);
            }
        },

        removeGroups: function(options) {
            var children = this.get('children');
            children.dispose();
            this.unset('children');
            this.set('conditions', new AutomationConditionCollection([{}], options));
        }
    });

    return AutomationGroupingsModel;
});