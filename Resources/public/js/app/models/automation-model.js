define([
    'oroui/js/app/models/base/model',
    'diamanteautomation/js/app/models/automation-actions-model',
    'diamanteautomation/js/app/models/automation-conditions-model'
],function (BaseModel, AutomationActionsModel, AutomationConditionsModel) {
    'use strict';

    var AutomationModel = BaseModel.extend({

        defaults: {
            name: '',
            timeInterval: ''
        },

        initialize: function(attr, options){
            this.set('actions', attr.actions ?
                new AutomationActionsModel(attr.actions, options) : new AutomationActionsModel({}, options));
            this.set('conditions', attr.conditions ?
                new AutomationConditionsModel(attr.conditions, options) : new AutomationConditionsModel({}, options));
        },

        validate: function(attrs, options) {

        }
    });

    return AutomationModel;
});