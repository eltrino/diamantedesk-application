define([
    'underscore',
    'tpl!diamanteautomation/js/app/templates/automation-template.ejs',
    'diamanteautomation/js/app/views/abstract/view'
],function (_, AutomationTemplate, AbstractView) {
    'use strict';

    var AutomationView = AbstractView.extend({
        className: 'container-fluid',
        template : AutomationTemplate,

        regions: {
            'automation-conditions': '#automation-conditions',
            'automation-actions': '#automation-actions'
        }
    });

    return AutomationView;
});