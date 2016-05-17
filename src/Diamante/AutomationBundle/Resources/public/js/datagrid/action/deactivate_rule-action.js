/*global define*/
define([
    './mass-action'
], function (MassAction) {
    'use strict';

    var DectivateRuleAction;

    DectivateRuleAction = MassAction.extend({
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