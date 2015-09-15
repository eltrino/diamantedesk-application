require(['jquery', 'underscore', 'orotranslation/js/translator', 'oroui/js/tools',
    'oroui/js/mediator', 'oroui/js/layout',
    'oroui/js/reset-password-confirmation',
    'bootstrap', 'jquery-ui'
], function ($, _, __, tools, mediator, layout, ResetPasswordConfirmation) {
    'use strict';

    $(function () {
        $(document).on('click', '.reset-password-button', function (e) {
            var el = $(this);
            if (!(el.is('[disabled]') || el.hasClass('disabled'))) {
                var confirm,
                    message = el.data('message');

                confirm = new ResetPasswordConfirmation({
                    content: message
                });

                confirm.on('ok', function () {
                    mediator.execute('showLoading');

                    $.ajax({
                        url: el.data('url'),
                        type: 'DELETE',
                        success: function (data) {
                            el.trigger('removesuccess');
                            var redirectTo = el.data('redirect');
                            if (redirectTo) {
                                mediator.execute('addMessage', 'success', el.data('success-message'));

                                // In case when redirectTo is current page just refresh it, otherwise redirect.
                                if (mediator.execute('compareUrl', redirectTo)) {
                                    mediator.execute('refreshPage');
                                } else {
                                    mediator.execute('redirectTo', {url: redirectTo});
                                }
                            } else {
                                mediator.execute('hideLoading');
                                mediator.execute('showFlashMessage', 'success', el.data('success-message'));
                            }
                        },
                        error: function () {
                            var message;
                            message = el.data('error-message') ||
                                __('Unexpected error occurred. Please contact system administrator.');
                            mediator.execute('hideLoading');
                            mediator.execute('showMessage', 'error', message);
                        }
                    });
                });
                confirm.open();
            }

            return false;
        });
    });

});