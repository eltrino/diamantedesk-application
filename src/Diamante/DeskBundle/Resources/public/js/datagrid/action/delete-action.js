define([
    'underscore',
    'oroui/js/messenger',
    'orotranslation/js/translator',
    'oroui/js/delete-confirmation',
    'oro/datagrid/action/model-action'
], function(_, messenger, __, DeleteConfirmation, ModelAction) {
    'use strict';

    var DeleteAction;

    /**
     * Delete action with confirm dialog, triggers REST DELETE request
     *
     * @export  oro/datagrid/action/delete-action
     * @class   oro.datagrid.action.DeleteAction
     * @extends oro.datagrid.action.ModelAction
     */
    DeleteAction = ModelAction.extend({

        /** @property {Function} */
        confirmModalConstructor: DeleteConfirmation,

        defaultMessages: {
            confirm_title: 'Delete Confirmation',
            confirm_content: 'Are you sure you want to delete this item?',
            confirm_ok: 'Yes, Delete',
            confirm_cancel: 'Cancel',
            success: 'Item deleted'
        },

        /**
         * Execute delete model
         */
        execute: function() {
            this.getConfirmDialog(_.bind(this.doDelete, this)).open();
        },

        /**
         * Confirm delete item
         */
        doDelete: function() {
            this.model.destroy({
                url: this.getLink(),
                wait: true,
                error: function(model, response) {
                    var messageText = __('You do not have permission to perform this action.');
                    try {
                        var json = JSON.parse(response.responseText);
                        if (typeof json.message != 'undefined') {
                            messageText = json.message;
                        }
                        if (typeof json.error != 'undefined') {
                            messageText = json.error;
                        }

                    }
                    catch(e) {
                    }
                    messenger.notificationFlashMessage('error', messageText);
                },
                success: function(model, response) {
                    var messageText = __('Item deleted');
                    messenger.notificationFlashMessage('success', messageText);
                }
            });
        }
    });

    return DeleteAction;
});
