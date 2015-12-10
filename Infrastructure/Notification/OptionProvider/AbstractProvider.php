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

namespace Diamante\AutomationBundle\Infrastructure\Notification\OptionProvider;


use Diamante\DeskBundle\Infrastructure\Notification\NotificationOptionsProvider;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;

abstract class AbstractProvider implements NotificationOptionsProvider
{
    const HTML_TEMPLATE = 'DiamanteAutomationBundle:Entity:Notification/notification.html.twig';
    const TXT_TEMPLATE = 'DiamanteAutomationBundle:Entity:Notification/notification.txt.twig';

    /**
     * @var DiamanteUser
     */
    protected $recipient;

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return string
     */
    public function getHtmlTemplate()
    {
        return self::HTML_TEMPLATE;
    }

    /**
     * @return string
     */
    public function getTxtTemplate()
    {
        return self::TXT_TEMPLATE;
    }

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
        if ($this->recipient instanceof DiamanteUser) {
            return $this->recipient->getFullName();
        }

        return $this->recipient->getFirstName() . ' ' . $this->recipient->getLastName();
        
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return static::SUBJECT_IDENTIFIER;
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
            'user'      => $this->getRecipientName()
        ];
    }
}