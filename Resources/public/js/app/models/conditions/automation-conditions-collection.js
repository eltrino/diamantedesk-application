define([
    'underscore',
    'diamanteautomation/js/app/models/conditions/automation-conditions-model',
    'oroui/js/app/models/base/collection'
],function (_, AutomationConditionsModel, BaseCollection) {
    'use strict';

    var AutomationConditionsCollection = BaseCollection.extend({
        model : AutomationConditionsModel,

        initialize : function(collection, options){
            if(options && options.parent){
                this.parent = options.parent;
            }
        }

    });

    return AutomationConditionsCollection;
});