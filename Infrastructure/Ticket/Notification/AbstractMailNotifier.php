<?php
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
namespace Diamante\DeskBundle\Infrastructure\Ticket\Notification;

use Diamante\DeskBundle\Model\Shared\DomainEvent;
use Swift_Mailer;
use Twig_Environment;

abstract class AbstractMailNotifier implements Notifier
{
    /**
     * @var Twig_Environment $twig
     */
    protected $twig;

    /**
     * @var Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     * @var string $senderEmail
     */
    protected $senderEmail;

    public function __construct(
        Twig_Environment $twig,
        Swift_Mailer $mailer,
        $senderEmail

    ) {
        $this->twig        = $twig;
        $this->mailer      = $mailer;
        $this->senderEmail = $senderEmail;
    }

    /**
     * @param DomainEvent $event
     */
    abstract function notify(DomainEvent $event);
} 