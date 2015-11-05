define(['backbone', './condition.model'
], function (Backbone, ConditionModel) {
    'use strict';

    return Backbone.Collection.extend({
        model: ConditionModel,

        initialize: function () {
            this.on('add', this.findParent);
        },

        findParent: function (model) {console.log('aaa');
            var rootModel = this.getRootModel(),
                group = this.getGroup();

            if (typeof rootModel == 'undefined') {
                if (group) {
                    model.set("isParent", true);

                    _.each(this.getConditions(), function(item) {
                        item.set('parent', model.getId(), {"silent": true});
                    });
                } else if (this.length > 1) {
                    var m = this.add({"expression": "AND", "target": "ticket"}, {"silent": true});
                    m.set("isParent", true);

                    _.each(this.getConditions(), function(item) {
                        item.set('parent', m.getId(), {"silent": true});
                    });
                }
            } else {
                model.set('parent', rootModel.getId(), {"silent": true});
            }
        },

        getRootModel: function () {
            return this.models.find(
                function (c) {
                    return c.has("isParent");
                });
        },

        getGroup: function () {
            return this.models.find(
                function (c) {
                    return c.has("expression");
                });
        },

        getConditions: function () {
            return this.models.filter(
                function (c) {
                    return c.has("condition");
                });
        }
    });
});
