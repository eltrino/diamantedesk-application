define([
    'oroui/js/app/models/base/model',
    'diamanteautomation/js/app/models/actions/automation-actions-collection',
    'diamanteautomation/js/app/models/conditions/automation-conditions-model'
],function (BaseModel, AutomationActionsCollection, AutomationConditionsModel) {
    'use strict';

    var AutomationModel = BaseModel.extend({

        defaults: {
            name: '',
            timeInterval: ''
        },

        initialize: function(attr){
            this.set('actions', attr.actions ?
                new AutomationActionsCollection(attr.actions) : new AutomationActionsCollection([{}]));
            this.set('conditions', attr.conditions ?
                new AutomationConditionsModel(attr.conditions) : new AutomationConditionsModel({}));
        },

        validate: function(attrs, options) {

        }
    });

    return AutomationModel;
});