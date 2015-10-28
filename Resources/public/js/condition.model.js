define(['underscore', 'backbone', './mock'], function (_, Backbone, Mock) {
    'use strict';

    return Backbone.Model.extend({
        initialize: function () {
            if (this.get('property')) {
                this.set('actionObject', 'property');
            } else {
                this.set('actionObject', 'entity');
            }

            this.on('remove', this.removeModel);
        }
    });
});
