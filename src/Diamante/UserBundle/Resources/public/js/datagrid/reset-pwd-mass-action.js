/*global define*/
define([
    'oroui/js/delete-confirmation',
    './mass-action'
], function (DeleteConfirmation, MassAction) {
    'use strict';

    var ResetPasswordMassAction;

    /**
     * Delete mass action class.
     *
     * @export  oro/datagrid/action/delete-mass-action
     * @class   oro.datagrid.action.DeleteMassAction
     * @extends oro.datagrid.action.MassAction
     */
    ResetPasswordMassAction = MassAction.extend({
        /** @property {Function} */
        confirmModalConstructor: DeleteConfirmation,

        /** @property {Object} */
        defaultMessages: {
            confirm_title: 'Reset Password Confirmation',
            confirm_content: 'Are you sure you want to reset password for selected customer(s)?',
            confirm_ok: 'Yes, Reset',
            confirm_cancel: 'Cancel',
            success: 'Password(s) for selected customer(s) successfully reset.',
            error: 'Error occurred while trying to reset password(s).',
            empty_selection: 'Please, select customer(s) to reset password.'
        }
    });

    return ResetPasswordMassAction;
});
