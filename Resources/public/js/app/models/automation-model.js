define([
    'oroui/js/app/models/base/model',
    'diamanteautomation/js/app/models/actions/automation-actions-collection',
    'diamanteautomation/js/app/models/conditions/automation-conditions-collection'
],function (BaseModel, AutomationActionsCollection, AutomationConditionsCollection) {
    'use strict';

    function flatten(data){
        for(var key in data){
            if(_.isObject(data[key])){
                data[key] = flatten(data[key])
            } else {
                data[key] = data[key];
            }
        }
        return data;
    }

    var AutomationModel = BaseModel.extend({

        defaults: {
            name: '',
            timeInterval: ''
        },

        initialize: function(attr){
            this.set('actions', attr.actions ?
                new AutomationActionsCollection(attr.actions) : new AutomationActionsCollection([{}]));
            this.set('conditions', attr.conditions ?
                new AutomationConditionsCollection(attr.conditions) : new AutomationConditionsCollection([{}]));
        },

        validate: function(attrs, options) {

        },

        serializePlain: function(){
            var result = BaseModel.prototype.serialize.apply(this);
            return flatten(result);
        }
    });

    return AutomationModel;
});