define(['oroui/js/app/models/base/model'
],function (BaseModel) {
    'use strict';

    var AutomationActionsModel = BaseModel.extend({

        defaults: {
            type: ''
        },

        initialize: function(attr, options){
            var parameters = attr.parameters;
            if(parameters){
                for(var key in parameters){
                    this.set({
                        property: key,
                        value: parameters[key]
                    })
                }
                this.unset('parameters');
            }
        },

        validate: function(attrs, options) {

        }
    });

    return AutomationActionsModel;
});