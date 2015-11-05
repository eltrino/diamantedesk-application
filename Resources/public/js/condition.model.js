define(['underscore', 'backbone', './mock'], function (_, Backbone, Mock) {
    'use strict';

    return Backbone.Model.extend({
        defaults: {
            "weight": 0,
            "active": 1
        },

        initialize: function () {
            if (!this.get('id')) {
                this.set('id', this.cid, {"silent": true});
            }

            if (this.get('property')) {
                this.set('actionObject', 'property');
            } else {
                this.set('actionObject', 'entity');
            }

            this.on('remove', this.removeModel);
        },

        getId: function () {
            if (this.get('id')) {
                return this.get('id');
            }

            return this.cid;
        }
    });
});
