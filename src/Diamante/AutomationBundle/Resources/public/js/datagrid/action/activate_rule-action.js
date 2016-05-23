/*global define*/
define([
    'diamanteautomation/js/datagrid/action/abstract_rule-action'
], function (AbstractRuleAction) {
    'use strict';

    var ActivateRuleAction;

    ActivateRuleAction = AbstractRuleAction.extend({
        defaultMessages: {
            confirm_title: 'Activate Confirmation',
            confirm_content: 'Are you sure you want to activate selected rules?',
            confirm_ok: 'Yes, Activate',
            confirm_cancel: 'Cancel',
            success: 'Rules are Activated.',
            error: 'Rules are not Activated.',
            empty_selection: 'Please, select rule to activate.'
        }
    });

    return ActivateRuleAction;
});