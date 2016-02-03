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
                this.cid = _.uniqueId('c');
            }
        },

        addSubCollection: function(collection, options){
            if(collection.parent){
                this.models.push(collection);
                this.length += collection.length;
                BaseCollection.prototype._addReference.call(this, collection, options);
                this.trigger('add', collection, this, options);
            }
        },

        destroy : function(options){
            this.trigger('destroy', this, this.parent, options);
        },

        getAttributes: function() {
            return this.serialize();
        }

    });

    return AutomationConditionsCollection;
});