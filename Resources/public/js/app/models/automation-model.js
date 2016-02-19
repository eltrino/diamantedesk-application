define([
    'underscore',
    'diamanteautomation/js/app/models/actions/automation-actions-collection',
    'diamanteautomation/js/app/models/groupings/automation-groupings-model',
    'oroui/js/app/models/base/model'
],function (_, AutomationActionsCollection, AutomationGroupingsModel, BaseModel) {
    'use strict';

    function flatten(data){
        for(var key in data){
            if(_.isObject(data[key]) && key != 'parameters'){
                data[key] = flatten(data[key])
            } else if(key === 'property' || key === 'value'){
                data.parameters = {};
                data.parameters[data['property']] = data['value'];
                delete data['property'];
                delete data['value'];
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
            // FIXME
            result['target'] = 'ticket';
            result = flatten(result);
            return result;
        }
    });

    return AutomationModel;
});