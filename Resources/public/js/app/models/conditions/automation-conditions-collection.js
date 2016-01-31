define([
    'diamanteautomation/js/app/models/conditions/automation-conditions-model',
    'oroui/js/app/models/base/collection'
],function (AutomationConditionsModel, BaseCollection) {
    'use strict';

    var AutomationConditionsCollection = BaseCollection.extend({
        model : AutomationConditionsModel,

        initialize : function(collection, options){
            if(options){
                this.parent = options.parent;
            }
        },

        add: function(models, options){
            if(!models.parent){
                console.log(arguments);
            }
            if(models.parent){
                this.models.push(models);
                this.length += models.length;
                this.trigger('add', models, this, options);
            } else {
                BaseCollection.prototype.add.apply(this, arguments);
            }
        }
    });

    return AutomationConditionsCollection;
});