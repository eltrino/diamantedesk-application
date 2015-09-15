/*global define*/
define(['underscore', 'orotranslation/js/translator', 'oroui/js/modal'
], function (_, __, Modal) {
    'use strict';

    /**
     * Reset password confirmation dialog
     *
     * @export  oroui/js/delete-confirmation
     * @class   oroui.ResetPasswordConfirmation
     * @extends oroui.Modal
     */
    return Modal.extend({
        /** @property {String} */
        className: 'modal oro-modal-danger',

        /** @property {String} */
        okButtonClass: 'btn-danger',

        /**
         * @param {Object} options
         */
        initialize: function (options) {
            options = _.extend({
                title: __('Password Reset Confirmation'),
                okText: __('Yes, Reset'),
                cancelText: __('Cancel')
            }, options);

            arguments[0] = options;
            Modal.prototype.initialize.apply(this, arguments);
        }
    });
});
