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

use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;

abstract class OptionsProvider
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var DiamanteUser
     */
    protected $recipient;

    /**
     * @param $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->recipient->getEmail();
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->userService->getFullName($this->recipient);
    }


    /**
     * @return array
     */
    public function getRequiredParams()
    {
        return ['delimiter', 'header', 'user', 'changes', 'attachments', 'ticketKey'];
    }

    /**
     * @return bool
     */
    public function subjectIsTranslatable()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getDefaultOptions()
    {
        return [
            'delimiter' => MessageReferenceServiceImpl::DELIMITER_LINE,
            'user'      => $this->getRecipientName(),
            'header'    => $this->getSubject()
        ];
    }

    /**
     * @return array
     */
    public function getHtmlOptions()
    {
        return ['html_options' => []];
    }

    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }
}
