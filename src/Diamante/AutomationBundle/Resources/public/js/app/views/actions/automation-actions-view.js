define([
    'jquery',
    'underscore',
    'tpl!diamanteautomation/js/app/templates/actions/automation-actions-template.ejs',
    'diamanteautomation/js/app/views/abstract/view'
],function ($, _, AutomationActionsTemplate, AbstractView) {
    'use strict';

    var AutomationActionsView = AbstractView.extend({
        template : AutomationActionsTemplate
    });

    return AutomationActionsView;
});