define(['underscore', 'backbone'], function (_, Backbone) {
    'use strict';

    return Backbone.Model.extend({
        initialize: function () {
            if (!this.get('id')) {
                this.set('id', this.cid, {"silent": true});
            }
        }
    });
});
