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

namespace Diamante\UserBundle\Infrastructure\Notification\OptionProvider;


use Diamante\DeskBundle\Infrastructure\Notification\NotificationOptionsProvider;
use Diamante\UserBundle\Entity\DiamanteUser;

class ForceReset implements NotificationOptionsProvider
{
    const HTML_TEMPLATE = 'DiamanteUserBundle:Notification:ForceReset/reset.html.twig';
    const TXT_TEMPLATE  = 'DiamanteUserBundle:Notification:ForceReset/reset.txt.twig';

    const SUBJECT_IDENTIFIER = 'diamante.user.notification.force_reset';

    /**
     * @var DiamanteUser
     */
    protected $recipient;

    /**
     * @return string
     */
    public function getName()
    {
        return 'force_reset';
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
        if (!($recipient instanceof DiamanteUser)) {
            throw new \RuntimeException('This notification should only be sent to Diamante Users');
        }
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
        return $this->recipient->getFullName();
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return self::SUBJECT_IDENTIFIER;
    }

    /**
     * @return array
     */
    public function getRequiredParams()
    {
        return ['activation_hash'];
    }

    /**
     * @return bool
     */
    public function subjectIsTranslatable()
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function getDefaultOptions()
    {
        return [];
    }
}