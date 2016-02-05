define([
    'diamanteautomation/js/app/models/actions/automation-actions-collection',
    'diamanteautomation/js/app/models/groupings/automation-groupings-model',
    'oroui/js/app/models/base/model'
],function (AutomationActionsCollection, AutomationGroupingsModel, BaseModel) {
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
            this.set('grouping', attr.grouping ?
                new AutomationGroupingsModel(attr.grouping) : new AutomationGroupingsModel({}));

            window.AutomationModel = this;
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