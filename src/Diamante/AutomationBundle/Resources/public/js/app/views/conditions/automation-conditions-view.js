define([
    'jquery',
    'underscore',
    'tpl!diamanteautomation/js/app/templates/conditions/automation-conditions-template.ejs',
    'diamanteautomation/js/app/views/abstract/view'
],function ($, _, AutomationConditionsTemplate, AbstractView) {
    'use strict';

    var AutomationConditionsView = AbstractView.extend({
        template : AutomationConditionsTemplate
    });

    return AutomationConditionsView;
});