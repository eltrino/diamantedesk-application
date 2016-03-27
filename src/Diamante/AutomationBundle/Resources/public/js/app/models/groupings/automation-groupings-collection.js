define([
    'underscore',
    'diamanteautomation/js/app/models/groupings/automation-groupings-model',
    'oroui/js/app/models/base/collection'
],function (_, AutomationGroupingsModel, BaseCollection) {
    'use strict';

    var AutomationGroupingsCollection = BaseCollection.extend({
        model : AutomationGroupingsModel
    });

    return AutomationGroupingsCollection;
});