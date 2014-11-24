define([
    'jquery',
    'underscore',
    'oroui/js/messenger'
], function ($, _, messenger) {
    'use strict';
    $(document).ajaxComplete(function (event, xhr) {
        if (!_.isEmpty(xhr.responseJSON) && !_.isEmpty(xhr.responseJSON.staticFlashMessages)) {
            _.each(xhr.responseJSON.staticFlashMessages, function (item, key) {
                _.each(item, function (message) {
                    messenger.notificationFlashMessage(key, message);
                });
            });
        }
    });
});