<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\DeskBundle\Infrastructure\Notification;


interface NotificationOptionsProvider
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getHtmlTemplate();

    /**
     * @return string
     */
    public function getTxtTemplate();

    /**
     * @return string
     */
    public function getRecipientEmail();

    /**
     * @return string
     */
    public function getRecipientName();

    /**
     * @return string
     */
    public function getSubject();

    public function setRecipient($recipient);

    /**
     * @return array
     */
    public function getRequiredParams();

    /**
     * @return bool
     */
    public function subjectIsTranslatable();
}