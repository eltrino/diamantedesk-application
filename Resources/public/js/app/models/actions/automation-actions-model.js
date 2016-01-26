define(['oroui/js/app/models/base/model'
],function (BaseModel) {
    'use strict';

    var AutomationActionsModel = BaseModel.extend({

        defaults: {
            name: '',
            entity: '',
            property: '',
            value: ''
        },

        initialize: function(attr, options){

        },

        validate: function(attrs, options) {

        }
    });

    return AutomationActionsModel;
});