/*global define*/
define([
    'diamanteautomation/js/datagrid/action/abstract_rule-action'
], function (AbstractRuleAction) {
    'use strict';

    var DectivateRuleAction;

    DectivateRuleAction = AbstractRuleAction.extend({
        defaultMessages: {
            confirm_title: 'Deactivate Confirmation',
            confirm_content: 'Are you sure you want to deactivate selected rules?',
            confirm_ok: 'Yes, Deactivate',
            confirm_cancel: 'Cancel',
            success: 'Rules are Deactivated.',
            error: 'Rules are not Deactivated.',
            empty_selection: 'Please, select rule to deactivate.'
        }
    });

    return DectivateRuleAction;
});