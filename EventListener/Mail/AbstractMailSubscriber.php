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
namespace Diamante\DeskBundle\EventListener\Mail;

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\AbstractTicketEvent;
use Swift_Mailer;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Twig_Environment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractMailSubscriber implements EventSubscriberInterface
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

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var string
     */
    protected $ticketId;

    /**
     * @var array
     */
    protected $recipientsList;

    /**
     * @var string
     */
    protected $messageSubject;

    /**
     * @var string
     */
    protected $messageHeader;

    public function __construct(
        Twig_Environment $twig,
        Swift_Mailer $mailer,
        SecurityFacade $securityFacade,
        $senderEmail
    ) {
        $this->twig             = $twig;
        $this->mailer           = $mailer;
        $this->securityFacade   = $securityFacade;
        $this->senderEmail      = $senderEmail;
    }

    /**
     * @param AbstractTicketEvent $event
     */
    protected function manageEvent(AbstractTicketEvent $event)
    {
        if (!$this->ticketId) {
            $this->ticketId = $event->getAggregateId();
            $this->messageSubject = $event->getSubject() . ':' . $event->getAggregateId();
            $this->setRecipientsList($event->getRecipientsList());
        }
    }

    /**
     * @param array $recipientsList
     */
    public function setRecipientsList(array $recipientsList)
    {
        $this->recipientsList = $recipientsList;
    }

    /**
     * @return string
     */
    protected function getUserFullName()
    {
        $user = $this->securityFacade->getLoggedUser();
        return $user->getFirstName() . ' ' . $user->getLastName();
    }

    /**
     * @param array $options
     * @param array $templates
     */
    protected function sendMessage(array $options, array $templates)
    {
        return ;
        $txtBody = $this->twig->render($templates['txt'],
            $options);

        $htmlBody = $this->twig->render($templates['html'],
            $options);

        $message = $this->mailer->createMessage();
        $message->setSubject($this->messageSubject);
        $message->setFrom($this->senderEmail, $this->getUserFullName());
        $message->setTo($this->recipientsList);
        $message->setReplyTo($this->senderEmail);
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');
        $this->mailer->send($message);
    }
}
