define(['oroui/js/app/models/base/model'
],function (BaseModel) {
    'use strict';

    var AutomationCoditionsModel = BaseModel.extend({

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

    return AutomationCoditionsModel;
});