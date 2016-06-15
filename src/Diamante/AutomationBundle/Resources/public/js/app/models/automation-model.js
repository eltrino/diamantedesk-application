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
                if(!data['value']){
                    data.parameters[data['property']] = data['type'];
                }
                delete data['property'];
                delete data['value'];
            } else if(key === 'entity_type') {
                if(data.entity_type) {
                    data.type = data.entity_type;
                }
                delete data['entity_type'];
            } else {
                data[key] = isNaN(data[key])? data[key] : +data[key];
            }
        }
        return data;
    }

    var AutomationModel = BaseModel.extend({

        defaults: {
            name: '',
            time_interval: '',
            target: ''
        },

        initialize: function(attr, options){
            var config = options.config;
            if(!attr.target) {
                this.set('target', _.keys(config.entities)[0]);
                options.target = _.keys(config.entities)[0];
            } else {
                options.target = attr.target
            }
            if(!attr.time_interval && attr.type === 'business') {
                this.set('time_interval', _.keys(config.time_intervals)[0])
            }
            delete options.model;
            options.isBusiness = attr.type === 'business';
            this.set('actions', attr.actions ?
                new AutomationActionsCollection(attr.actions, options) :
                new AutomationActionsCollection([{}], options));
            this.set('grouping', attr.grouping ?
                new AutomationGroupingsModel(attr.grouping, options) :
                new AutomationGroupingsModel({}, options));

            window.AutomationModel = this;
        },

        validate: function(attrs, options) {

        },

        serializePlain: function(){
            var result = BaseModel.prototype.serialize.apply(this);
            result = flatten(result);
            return result;
        }
    });

    return AutomationModel;
});