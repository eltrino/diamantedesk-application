/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */
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