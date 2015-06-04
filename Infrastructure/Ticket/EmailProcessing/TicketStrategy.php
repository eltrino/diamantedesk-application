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
namespace Diamante\DeskBundle\Infrastructure\Ticket\EmailProcessing;

use Diamante\DeskBundle\Api\Internal\WatchersServiceImpl;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceService;
use Diamante\DeskBundle\Api\BranchEmailConfigurationService;
use Diamante\DeskBundle\EventListener\TicketNotificationsSubscriber;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\EmailProcessingBundle\Model\Mail\SystemSettings;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Processing\Strategy;
use Diamante\UserBundle\Infrastructure\DiamanteUserFactory;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\UserBundle\Entity\UserManager as OroUserManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TicketStrategy implements Strategy
{

    const EMAIL_NOTIFIER_CONFIG_PATH = 'oro_notification.email_notification_sender_email';

    /**
     * @var MessageReferenceService
     */
    private $messageReferenceService;

    /**
     * @var BranchEmailConfigurationService
     */
    private $branchEmailConfigurationService;

    /**
     * @var TicketNotificationsSubscriber
     */
    private $ticketNotificationsSubscriber;

    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var DiamanteUserFactory
     */
    private $diamanteUserFactory;

    /**
     * @var SystemSettings
     */
    private $emailProcessingSettings;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var WatchersServiceImpl
     */
    private $watchersService;

    /**
     * @var OroUserManager
     */
    private $oroUserManager;

    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param MessageReferenceService $messageReferenceService
     * @param BranchEmailConfigurationService $branchEmailConfigurationService
     * @param DiamanteUserRepository $diamanteUserRepository
     * @param DiamanteUserFactory $diamanteUserFactory
     * @param SystemSettings $settings
     * @param TicketNotificationsSubscriber $ticketNotificationsSubscriber
     * @param EventDispatcher $eventDispatcher
     * @param WatchersServiceImpl $watchersService
     * @param OroUserManager $oroUserManager
     * @param ConfigManager $configManager
     */
    public function __construct(MessageReferenceService $messageReferenceService,
                                BranchEmailConfigurationService $branchEmailConfigurationService,
                                DiamanteUserRepository $diamanteUserRepository,
                                DiamanteUserFactory $diamanteUserFactory,
                                SystemSettings $settings,
                                TicketNotificationsSubscriber $ticketNotificationsSubscriber,
                                EventDispatcher $eventDispatcher,
                                WatchersServiceImpl $watchersService,
                                OroUserManager $oroUserManager,
                                ConfigManager $configManager)
    {
        $this->messageReferenceService         = $messageReferenceService;
        $this->branchEmailConfigurationService = $branchEmailConfigurationService;
        $this->diamanteUserRepository          = $diamanteUserRepository;
        $this->diamanteUserFactory             = $diamanteUserFactory;
        $this->emailProcessingSettings         = $settings;
        $this->ticketNotificationsSubscriber   = $ticketNotificationsSubscriber;
        $this->eventDispatcher                 = $eventDispatcher;
        $this->watchersService                 = $watchersService;
        $this->oroUserManager                  = $oroUserManager;
        $this->configManager                   = $configManager;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $email = $message->getFrom()->getEmail();
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($email);
        $type = User::TYPE_DIAMANTE;

        if (is_null($diamanteUser)) {
            $sender = $message->getFrom();
            $diamanteUser = $this->diamanteUserFactory->create($email, $sender->getFirstName(), $sender->getLastName());

            $this->diamanteUserRepository->store($diamanteUser);
        }

        $reporterId = $diamanteUser->getId();

        $reporter = new User($reporterId, $type);

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
            $branchId = $this->getAppropriateBranch($message->getFrom()->getEmail(), $message->getTo());
            $assigneeId = $this->branchEmailConfigurationService->getBranchDefaultAssignee($branchId);

            $ticket = $this->messageReferenceService->createTicket($message->getMessageId(), $branchId,
                $message->getSubject(), $message->getContent(), $reporter, $assigneeId, $attachments);
        } else {
            $this->eventDispatcher->removeListener(
                'commentWasAddedToTicket',
                array(
                    $this->ticketNotificationsSubscriber,
                    'processEvent'
                )
            );
            $ticket = $this->messageReferenceService->createCommentForTicket($message->getContent(), $reporter,
                $message->getReference(), $attachments);
        }

        $this->processWatchers($message, $ticket);
    }

    /**
     * @param $from
     * @param $to
     * @return int
     */
    private function getAppropriateBranch($from, $to)
    {
        $branchId = null;
        preg_match('/@(.*)/', $from, $output);

        if (isset($output[1])) {
            $customerDomain = $output[1];

            $branchId = $this->branchEmailConfigurationService
                ->getConfigurationBySupportAddressAndCustomerDomain($to, $customerDomain);
        }
        if (!$branchId) {
            $branchId = $this->emailProcessingSettings->getDefaultBranchId();
        }

        return $branchId;
    }

    /**
     * @param Message $message
     * @param Ticket $ticket
     */
    private function processWatchers(Message $message, $ticket)
    {
        if (!$ticket) {
            return;
        }

        /** @var Message\MessageRecipient $recipient */
        foreach ($message->getRecipients() as $recipient) {

            $email = $recipient->getEmail();

            if ($email == $this->configManager->get(self::EMAIL_NOTIFIER_CONFIG_PATH)) {
                continue;
            }

            $diamanteUser = $this->diamanteUserRepository->findUserByEmail($email);
            $oroUser = $this->oroUserManager->findUserByEmail($email);

            if ($oroUser) {
                $user = new User($oroUser->getId(), User::TYPE_ORO);
            } elseif ($diamanteUser) {
                $user = new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
            } else {
                $diamanteUser = $this->diamanteUserFactory->create($email, $recipient->getFirstName(),
                    $recipient->getLastName());
                $this->diamanteUserRepository->store($diamanteUser);
                $user = new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
            }

            $this->watchersService->addWatcher($ticket, $user);
        }
    }
}
