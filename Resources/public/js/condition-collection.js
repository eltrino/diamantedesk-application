define(['backbone', './condition-model'
    ], function(Backbone, ConditionModel) {
    'use strict';

    var ConditionCollection = Backbone.Collection.extend({
        model: ConditionModel
    });

    return ConditionCollection;
});
