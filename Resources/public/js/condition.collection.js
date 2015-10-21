define(['backbone', './condition.model'
    ], function(Backbone, ConditionModel) {
    'use strict';

    return Backbone.Collection.extend({
        model: ConditionModel
    });
});
