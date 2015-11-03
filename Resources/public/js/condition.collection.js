define(['backbone', './condition.model'
], function (Backbone, ConditionModel) {
    'use strict';

    return Backbone.Collection.extend({
        model: ConditionModel,

        initialize: function () {
            this.on('add', this.findParent);
        },

        findParent: function (model) {
            var rootModel = this.getRootModel(),
                group = this.getGroup();

            if (typeof rootModel == 'undefined') {
                if (group) {
                    model.set("isParent", true);

                    _.each(this.getConditions(), function(item) {
                        item.set('parent', model.cid, {"silent": true});
                    });

                    //console.log('group add');
                } else if (this.length > 1) {
                    var m = this.add({"expression": "AND"}, {"silent": true});
                    m.set("isParent", true);

                    _.each(this.getConditions(), function(item) {
                        item.set('parent', m.cid, {"silent": true});
                    });

                    //console.log('more the 1');
                }
            } else {
                model.set('parent', rootModel.cid, {"silent": true});
            }

            //console.log(this.models);
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
