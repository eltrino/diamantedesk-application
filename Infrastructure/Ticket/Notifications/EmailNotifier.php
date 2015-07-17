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

use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Diamante\DeskBundle\Model\Shared\Email\TemplateResolver;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notifier;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Model\DiamanteUser;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\DeskBundle\Model\Shared\Notification;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Diamante\DeskBundle\Api\WatchersService;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;

class EmailNotifier implements Notifier
{

    const EMAIL_NOTIFIER_CONFIG_PATH = 'oro_notification.email_notification_sender_email';

    /**
     * @var Container
     */
    private $container;

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
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @var string
     */
    private $senderHost;

    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var UserManager
     */
    private $oroUserManager;

    /**
     * @var WatchersService
     */
    private $watchersService;

    /**
     * @param Container                  $container
     * @param \Twig_Environment          $twig
     * @param \Swift_Mailer              $mailer
     * @param TemplateResolver           $templateResolver
     * @param TicketRepository           $ticketRepository
     * @param MessageReferenceRepository $messageReferenceRepository
     * @param UserService                $userService
     * @param NameFormatter              $nameFormatter
     * @param DiamanteUserRepository     $diamanteUserRepository
     * @param ConfigManager              $configManager
     * @param UserManager                $userManager
     * @param WatchersService            $watchersService
     * @param                            $senderHost
     */
    public function __construct(
        Container $container,
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        TemplateResolver $templateResolver,
        TicketRepository $ticketRepository,
        MessageReferenceRepository $messageReferenceRepository,
        UserService $userService,
        NameFormatter $nameFormatter,
        DiamanteUserRepository $diamanteUserRepository,
        ConfigManager $configManager,
        UserManager $userManager,
        WatchersService $watchersService,
        $senderHost
    )
    {
        $this->container                    = $container;
        $this->twig                         = $twig;
        $this->mailer                       = $mailer;
        $this->templateResolver             = $templateResolver;
        $this->ticketRepository             = $ticketRepository;
        $this->messageReferenceRepository   = $messageReferenceRepository;
        $this->userService                  = $userService;
        $this->nameFormatter                = $nameFormatter;
        $this->diamanteUserRepository       = $diamanteUserRepository;
        $this->configManager                = $configManager;
        $this->oroUserManager               = $userManager;
        $this->watchersService              = $watchersService;
        $this->senderHost                   = $senderHost;
    }

    /**
     * @param Notification $notification
     * @return void
     */
    public function notify(Notification $notification)
    {
        if (!$this->container->isScopeActive('request')) {
            $this->container->enterScope('request');
            $this->container->set('request', new Request(), 'request');
        }

        $ticket = $this->loadTicket($notification);
        $changeList = $this->postProcessChangesList($notification);

        foreach ($this->watchersService->getWatchers($ticket) as $watcher) {
            $userType = $watcher->getUserType();
            $user = User::fromString($userType);
            $isOroUser = $user->isOroUser();
            if($isOroUser) {
                $loadedUser = $this->oroUserManager->findUserBy(['id' => $user->getId()]);
            } else {
                $loadedUser = $this->diamanteUserRepository->get($user->getId());
            }
            $message = $this->message($notification, $ticket, $isOroUser, $loadedUser->getEmail(), $changeList);
            $this->mailer->send($message);
            $reference = new MessageReference($message->getId(), $ticket);
            $this->messageReferenceRepository->store($reference);
        }
    }

    /**
     * @param Notification $notification
     * @param Ticket       $ticket
     * @param bool         $isOroUser
     * @param string       $recipientEmail
     * @param              $changeList
     *
     * @return \Swift_Message
     */
    private function message(Notification $notification, Ticket $ticket, $isOroUser, $recipientEmail, $changeList)
    {
        $senderEmail = $this->configManager->get(self::EMAIL_NOTIFIER_CONFIG_PATH);
        $userFormattedName = $this->getFormattedUserName($notification, $ticket);

        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $message->setSubject($this->decorateMessageSubject($notification->getSubject(), $ticket));
        $message->setFrom($senderEmail, $userFormattedName);
        $message->setTo($recipientEmail);
        $message->setReplyTo($senderEmail);

        $headers = $message->getHeaders();
        $headers->addTextHeader('In-Reply-To', $this->inReplyToHeader($notification));
        $headers->addIdHeader('References', $this->referencesHeader($ticket));

        $options = array(
            'changes'       => $changeList,
            'attachments'   => $notification->getAttachments(),
            'user'          => $userFormattedName,
            'header'        => $notification->getHeaderText(),
            'delimiter'     => MessageReferenceServiceImpl::DELIMITER_LINE,
            'isOroUser'     => $isOroUser,
            'ticketKey'     => $ticket->getKey()
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

        if ($reporter instanceof DiamanteUser) {
            $emails[$reporter->getEmail()] = $reporter->getFullName();
        } else {
            $emails[$reporter->getEmail()] = $reporter->getFirstName() . ' ' . $reporter->getLastName();
        }

        if ($assignee) {
            $emails[$assignee->getEmail()] = $assignee->getFirstName() . ' ' . $assignee->getLastName();
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
        return sprintf('[%s] %s', (string)$ticket->getKey(), $subject);
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
     * @return \Diamante\UserBundle\Entity\DiamanteUser|\Oro\Bundle\UserBundle\Entity\User
     */
    private function getUserDependingOnType($user)
    {
        if ($user instanceof OroUser) {
            return $user;
        }

        if ($user instanceof ApiUser) {
            $userId = $this->userService->verifyDiamanteUserExists($user->getEmail());
            $user = empty($userId) ? $user : new User($userId, User::TYPE_DIAMANTE);
        }

        $result = $this->userService->getByUser($user);

        return $result;
    }

    /**
     * @param Notification $notification
     * @return \ArrayAccess
     */
    private function postProcessChangesList(Notification $notification)
    {
        $changes = $notification->getChangeList();

        if (isset($changes['Reporter']) && strpos($changes['Reporter'], '_')) {
            $r = $changes['Reporter'];
            $u = User::fromString($r);
            $details = $this->userService->fetchUserDetails($u);
            $changes['Reporter'] = $details->getFullName();
        }

        return $changes;
    }

    /**
     * @param Notification $notification
     * @param Ticket $ticket
     *
     * @return string
     */
    private function getFormattedUserName(Notification $notification, Ticket $ticket)
    {
        $author = $notification->getAuthor();
        if(is_null($author)) {
            $reporterId = $ticket->getReporter()->getId();
            $user = $this->diamanteUserRepository->get($reporterId);
        } else {
            $user = $this->getUserDependingOnType($author);
        }
        $name = $this->nameFormatter->format($user);

        if (empty($name)) {
            $format = $this->nameFormatter->getNameFormat();

            $name = str_replace(
                array('%first_name%','%last_name%','%prefix%','%middle_name%','%suffix%'),
                array($user->getFirstName(), $user->getLastName(),'','',''),
                $format
            );
        }

        $name = preg_replace('/\s+/', ' ',$name);

        return trim($name);
    }
}
