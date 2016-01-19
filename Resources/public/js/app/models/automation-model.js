define(['oroui/js/app/models/base/model'],function (BaseModel) {
    'use strict';

    var AutomationModel = BaseModel.extend({

        defaults: {
            name: '',
            timeInterval: ''
        },

        initialize: function(options){

        },

        validate: function(attrs, options) {

        }
    });

    return AutomationModel;
});