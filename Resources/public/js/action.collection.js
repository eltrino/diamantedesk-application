define(['backbone', './action.model'
    ], function(Backbone, ActionModel) {
    'use strict';

    return Backbone.Collection.extend({
        model: ActionModel
    });
});
