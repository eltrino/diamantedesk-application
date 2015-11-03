define(['underscore', 'backbone', './mock'], function (_, Backbone, Mock) {
    'use strict';

    return Backbone.Model.extend({
        idAttribute: 'cid',

        initialize: function () {
            this.set('id', this.cid, {"silent": true});
            if (this.get('property')) {
                this.set('actionObject', 'property');
            } else {
                this.set('actionObject', 'entity');
            }

            this.on('remove', this.removeModel);
        }
    });
});
