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
use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Diamante\DeskBundle\Model\Ticket\Notifications\Email\TemplateResolver;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notification;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notifier;
use Diamante\DeskBundle\Model\Ticket\Notifications\Resolver\ReporterFullNameResolver;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Model\User\UserDetailsService;
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
     * @var MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var UserService
     */
    private $userService;

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

    /**
     * @var UserDetailsService
     */
    private $userDetailsService;

    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TemplateResolver $templateResolver,
        TicketRepository $ticketRepository,
        MessageReferenceRepository $messageReferenceRepository,
        UserService $userService,
        NameFormatter $nameFormatter,
        UserDetailsService $userDetailsService,
        $senderEmail,
        $senderHost
    )
    {
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->templateResolver = $templateResolver;
        $this->ticketRepository = $ticketRepository;
        $this->messageReferenceRepository = $messageReferenceRepository;
        $this->userService = $userService;
        $this->nameFormatter = $nameFormatter;
        $this->userDetailsService = $userDetailsService;
        $this->senderEmail = $senderEmail;
        $this->senderHost = $senderHost;
    }

    /**
     * @param Notification $notification
     * @return void
     */
    public function notify(Notification $notification)
    {
        $ticket = $this->loadTicket($notification);
        $message = $this->message($notification, $ticket);

        $this->mailer->send($message);

        $reference = new MessageReference($message->getId(), $ticket);
        $this->messageReferenceRepository->store($reference);
    }

    /**
     * @param Notification $notification
     * @return \Swift_Message
     */
    private function message(Notification $notification, Ticket $ticket)
    {
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
        $headers->addIdHeader('References', $this->referencesHeader($ticket));

        $changeList = $this->postProcessChangesList($notification);

        $options = array(
            'changes'       => $changeList,
            'attachments'   => $notification->getAttachments(),
            'user'          => $userFormattedName,
            'header'        => $notification->getHeaderText(),
            'delimiter'     => MessageReferenceServiceImpl::DELIMITER_LINE
        );

        $txtTemplate = $this->templateResolver->resolve($notification, TemplateResolver::TYPE_TXT);
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
        $reporter = $this->userService->getByUser($reporter);
        $assignee = $ticket->getAssignee();

        $emails[] = $reporter->getEmail();

        if ($assignee) {
            $emails[] = $assignee->getEmail();
        }

        return $emails;
    }

    /**
     * @param Ticket $ticket
     * @return array
     */
    private function referencesHeader(Ticket $ticket)
    {
        $ids = array();
        foreach ($this->messageReferenceRepository->findAllByTicket($ticket) as $reference) {
            $ids[] = $reference->getMessageId();
        }
        return $ids;
    }

    /**
     * @param string $subject
     * @param Ticket $ticket
     * @return string
     */
    private function decorateMessageSubject($subject, Ticket $ticket)
    {
        return sprintf('%s %s', (string)$ticket->getKey(), $subject);
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

    /**
     * @param $user
     * @return \Diamante\ApiBundle\Entity\ApiUser|\Oro\Bundle\UserBundle\Entity\User
     */
    private function getUserDependingOnType($user)
    {
        if ($user instanceof User) {
            if ($user->isApiUser()) {
                $user = $this->userService->getByUser($user);
            }
        }

        return $user;
    }

    /**
     * @param Notification $notification
     * @return \ArrayAccess
     */
    private function postProcessChangesList(Notification $notification)
    {
        $changes = $notification->getChangeList();

        if (isset($changes['Reporter'])) {
            $details = $this->userDetailsService->fetch(User::fromString($changes['Reporter']));
            $changes['Reporter'] = $details->getFullName();
        }

        return $changes;
    }
}
