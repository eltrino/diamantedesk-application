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
namespace Diamante\DeskBundle\Infrastructure\Ticket\Notifications;

use Diamante\DeskBundle\Model\Shared\UserService;
use Diamante\DeskBundle\Model\Ticket\Notifications\Email\TemplateResolver;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notification;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notifier;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\DeskBundle\Model\User\User;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

class EmailNotifier implements Notifier
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var TemplateResolver
     */
    private $templateResolver;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var UserService
     */
    private $userUservice;

    /**
     * @var NameFormatter
     */
    private $nameFormatter;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $senderHost;

    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TemplateResolver $templateResolver,
        TicketRepository $ticketRepository,
        UserService $userService,
        NameFormatter $nameFormatter,
        $senderEmail,
        $senderHost
    ) {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->templateResolver = $templateResolver;
        $this->ticketRepository = $ticketRepository;
        $this->userUservice = $userService;
        $this->nameFormatter = $nameFormatter;
        $this->senderEmail = $senderEmail;
        $this->senderHost = $senderHost;
    }

    /**
     * @param Notification $notification
     * @return void
     */
    public function notify(Notification $notification)
    {
        $message = $this->message($notification);
        $this->mailer->send($message);
    }

    /**
     * @param Notification $notification
     * @return \Swift_Message
     */
    private function message(Notification $notification)
    {
        $ticket = $this->loadTicket($notification);

        $user = $this->getUserDependingOnType($notification->getAuthor());
        $userFormattedName = $this->nameFormatter->format($user);

        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $message->setSubject($this->decorateMessageSubject($notification->getSubject(), $ticket));
        $message->setFrom($this->senderEmail, $userFormattedName);
        $message->setTo($this->resolveRecipientsEmails($ticket));
        $message->setReplyTo($this->senderEmail);

        $headers = $message->getHeaders();
        $headers->addTextHeader('In-Reply-To', $this->inReplyToHeader($notification));

        $options = array (
            'changes'     => $notification->getChangeList(),
            'attachments' => $notification->getAttachments(),
            'user'        => $userFormattedName,
            'header'      => $notification->getHeaderText()
        );

        $txtTemplate  = $this->templateResolver->resolve($notification, TemplateResolver::TYPE_TXT);
        $htmlTemplate = $this->templateResolver->resolve($notification, TemplateResolver::TYPE_HTML);

        $message->setBody($this->twig->render($txtTemplate, $options), 'text/plain');
        $message->addPart($this->twig->render($htmlTemplate, $options), 'text/html');

        return $message;
    }

    /**
     * @param Ticket $ticket
     * @return array
     */
    private function resolveRecipientsEmails(Ticket $ticket)
    {
        $emails = array();
        $reporter = $ticket->getReporter();
        $reporter = $this->userUservice->getByUser($reporter);
        $assignee = $ticket->getAssignee();

        $emails[] = $reporter->getEmail();

        if ($assignee) {
            $emails[] = $assignee->getEmail();
        }

        return $emails;
    }

    /**
     * @param string $subject
     * @param Ticket $ticket
     * @return string
     */
    private function decorateMessageSubject($subject, Ticket $ticket)
    {
        return sprintf('%s %s', (string) $ticket->getKey(), $subject);
    }

    /**
     * @param Notification $notification
     * @return string
     */
    private function inReplyToHeader(Notification $notification)
    {
        return ' <' . $notification->getTicketUniqueId() . '.' . $this->senderHost . '>';
    }

    /**
     * @param Notification $notification
     * @return Ticket
     */
    private function loadTicket(Notification $notification)
    {
        $uniqueId = new UniqueId($notification->getTicketUniqueId());
        $ticket = $this->ticketRepository->getByUniqueId($uniqueId);
        return $ticket;
    }

    private function getUserDependingOnType($user)
    {
        if ($user instanceof User) {
            if ($user->isApiUser()) {
                $user = $this->userUservice->getByUser($user);
            }
        }

        return $user;
    }
}
