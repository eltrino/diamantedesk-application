define([
    'diamanteautomation/js/app/models/actions/automation-actions-model',
    'oroui/js/app/models/base/collection'
],function (AutomationActionsModel, BaseCollection) {
    'use strict';

    var AutomationActionsCollection = BaseCollection.extend({
        model : AutomationActionsModel
    });

    return AutomationActionsCollection;
});