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
            confirm_content: 'Are you sure you want to reset password for selected users?',
            confirm_ok: 'Yes, Reset',
            confirm_cancel: 'Cancel',
            success: 'Successfully reset password for selected users',
            error: 'Error occured while trying to reset passwords.',
            empty_selection: 'Please, select users to reset passwords for.'
        }
    });

    return ResetPasswordMassAction;
});
