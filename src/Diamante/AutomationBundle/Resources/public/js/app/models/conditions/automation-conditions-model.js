define(['oroui/js/app/models/base/model'
],function (BaseModel) {
    'use strict';

    var AutomationConditionsModel = BaseModel.extend({

        defaults: {
            entity: ''
        },

        initialize: function(attr, options){
            var parameters = attr.parameters;
            if(parameters){
                for(var key in parameters){
                    if(key == attr.type){
                        this.set({
                            entity_type : attr.type,
                            property: key,
                            value: 'true'
                        });
                    } else if ('assignee' == key && options.edit && 'property_removed' == parameters[key]) {
                        this.set({
                            property: key,
                            value: 'unassigned'
                        });
                    } else {
                        this.set({
                            property: key,
                            value: parameters[key]
                        });
                    }
                }
                this.unset('parameters');
            }
            if(options.target){
                this.set('entity', options.target);
            }
        },

        validate: function(attrs, options) {

        }
    });

    return AutomationConditionsModel;
});