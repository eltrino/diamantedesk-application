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

class TicketWasUpdatedMailNotifier extends AbstractMailNotifier
{
    public function notify(DomainEvent $event)
    {
        $changes = $event->getChanges();

        $txtBody  = $this->twig->render('DiamanteDeskBundle:Ticket/notification/mails/update:notification.txt.twig', array('changes' => $changes));
        $htmlBody = $this->twig->render('DiamanteDeskBundle:Ticket/notification/mails/update:notification.html.twig', array('changes' => $changes));

        $message = $this->mailer->createMessage();
        $message->setSubject('Ticket was updated');
        $message->setFrom($this->senderEmail);
        $message->setTo($this->senderEmail);
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');

        $this->mailer->send($message);
    }
} 