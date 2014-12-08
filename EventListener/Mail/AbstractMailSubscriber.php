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
use Diamante\DeskBundle\Model\User\UserDetailsService;
use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Model\User\UserDetails;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
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
     * @var string
     */
    protected $senderHost;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    protected $securityFacade;

    /**
     * @var string
     */
    protected $ticketId;

    /**
     * @var TicketRepository
     */
    protected $ticketRepository;

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

    /**
     * @var UserDetailsService
     */
    protected $userDetailsService;

    public function __construct(
        Twig_Environment $twig,
        Swift_Mailer $mailer,
        SecurityFacade $securityFacade,
        ConfigManager $configManager,
        TicketRepository $ticketRepository,
        UserDetailsService $userDetailsService,
        $senderEmail,
        $senderHost
    ) {
        $this->twig             = $twig;
        $this->mailer           = $mailer;
        $this->securityFacade   = $securityFacade;
        $this->senderEmail      = $senderEmail;
        $this->senderHost       = $senderHost;
        $this->configManager    = $configManager;
        $this->ticketRepository = $ticketRepository;
        $this->userDetailsService = $userDetailsService;
    }

    /**
     * @param AbstractTicketEvent $event
     */
    protected function manageEvent(AbstractTicketEvent $event)
    {
        if (!$this->ticketId) {
            $this->ticketId = $event->getAggregateId();
            $this->messageSubject = $event->getSubject();
            $this->setRecipientsList($this->processRecipientsList($event->getRecipientsList()));
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

        if ($user instanceof User) {
            $userObj = new User($user->getId(), User::TYPE_DIAMANTE);
        } else {
            $userObj = new User($user->getId(), User::TYPE_ORO);
        }

        $userDetails = $this->getUserDetails($userObj);
        return $userDetails->getFullName();
    }

    /**
     * @param array $options
     * @param array $templates
     */
    protected function sendMessage(array $options, array $templates)
    {
        $emailNotificationsEnabled = (bool)$this->configManager->get('diamante_desk.email_notification');
        if ( $emailNotificationsEnabled === false ) {
            return;
        }

        $txtBody = $this->twig->render($templates['txt'],
            $options);

        $htmlBody = $this->twig->render($templates['html'],
            $options);

        $ticketKey = $this->getTicketKey();

        /** @var \Swift_Message $message */
        $message = $this->mailer->createMessage();
        $headers = $message->getHeaders();
        $listIdHeaderValue = $this->generateInReplyToHeader($ticketKey);
        if ($listIdHeaderValue) {
            $headers->addTextHeader('In-Reply-To', $listIdHeaderValue);
        }
        $message->setSubject($this->decorateMessageSubject($ticketKey, $this->messageSubject));
        $message->setFrom($this->senderEmail, $this->getUserFullName());
        $message->setTo($this->recipientsList);
        $message->setReplyTo($this->senderEmail);
        $message->setBody($txtBody, 'text/plain');
        $message->addPart($htmlBody, 'text/html');
        $this->mailer->send($message);
    }

    /**
     * @param User $user
     * @return UserDetails
     */
    protected function getUserDetails(User $user)
    {
        $details = $this->userDetailsService->fetch($user);

        return $details;
    }

    /**
     * @param array $list
     * @return array
     */
    protected function processRecipientsList(array $list)
    {
        $result = array();

        foreach ($list as $user) {
            $user = User::fromString($user);
            $details = $this->getUserDetails($user);
            $result[] = $details->getEmail();
        }

        return $result;
    }

    /**
     * Generate 'List-Id' header that is unique by ticket
     * @param TicketKey $ticketKey
     * @return null|string
     */
    protected function generateInReplyToHeader(TicketKey $ticketKey)
    {
        if ($ticketKey) {
            return ' <' . $this->ticketId->getValue() . '.' . $this->senderHost . '>';
        }
        return null;
    }

    /**
     * @param TicketKey $ticketKey
     * @param string $subject
     * @return string|null
     */
    protected function decorateMessageSubject(TicketKey $ticketKey, $subject)
    {
        if ($ticketKey) {
            return $ticketKey . ' ' . $subject;
        }
        return $subject;
    }

    /**
     * @return TicketKey
     */
    protected function getTicketKey()
    {
        $ticket = $this->ticketRepository->getByUniqueId($this->ticketId);
        return $ticket->getKey();
    }
}
