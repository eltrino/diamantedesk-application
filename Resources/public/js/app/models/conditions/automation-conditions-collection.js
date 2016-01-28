define([
    'diamanteautomation/js/app/models/conditions/automation-conditions-model',
    'oroui/js/app/models/base/collection'
],function (AutomationConditionsModel, BaseCollection) {
    'use strict';

    var AutomationConditionsCollection = BaseCollection.extend({
        model : AutomationConditionsModel
    });

    return AutomationConditionsCollection;
});