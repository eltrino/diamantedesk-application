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
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\EmailProcessingBundle\Model\Mail\SystemSettings;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Processing\Strategy;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\UserBundle\Entity\UserManager as OroUserManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var SystemSettings
     */
    private $emailProcessingSettings;

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
     * @param SystemSettings $settings
     * @param WatchersServiceImpl $watchersService
     * @param OroUserManager $oroUserManager
     * @param ConfigManager $configManager
     * @param UserService $diamanteUserService
     */
    public function __construct(MessageReferenceService $messageReferenceService,
                                BranchEmailConfigurationService $branchEmailConfigurationService,
                                SystemSettings $settings,
                                WatchersServiceImpl $watchersService,
                                OroUserManager $oroUserManager,
                                ConfigManager $configManager,
                                UserService $diamanteUserService
    )
    {
        $this->messageReferenceService         = $messageReferenceService;
        $this->branchEmailConfigurationService = $branchEmailConfigurationService;
        $this->emailProcessingSettings         = $settings;
        $this->watchersService                 = $watchersService;
        $this->oroUserManager                  = $oroUserManager;
        $this->configManager                   = $configManager;
        $this->diamanteUserService             = $diamanteUserService;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $diamanteUser = $this->diamanteUserService->getUserByEmail($message->getFrom()->getEmail());

        if (is_null($diamanteUser) || !$diamanteUser->isDiamanteUser()) {
            $id = $this->diamanteUserService->createDiamanteUser($this->prepareCreateUserCommand($message));
            $diamanteUser = new User($id, User::TYPE_DIAMANTE);
        }

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
            $branchId = $this->getAppropriateBranch($message->getFrom()->getEmail(), $message->getTo());
            $assigneeId = $this->branchEmailConfigurationService->getBranchDefaultAssignee($branchId);

            $ticket = $this->messageReferenceService->createTicket($message->getMessageId(), $branchId,
                $message->getSubject(), $message->getContent(), (string)$diamanteUser, $assigneeId, $attachments);
        } else {
            $ticket = $this->messageReferenceService->createCommentForTicket($message->getContent(), (string)$diamanteUser,
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
        if (null === $branchId) {
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

            $user = $this->diamanteUserService->getUserByEmail($email);

            if (is_null($user)) {
                $user = $this->diamanteUserService->createDiamanteUser($this->prepareCreateUserCommand($recipient));
            }

            $this->watchersService->addWatcher($ticket, $user);
        }
    }


    /**
     * @param Message\MessageRecipient $recipient
     * @return CreateDiamanteUserCommand
     */
    protected function prepareCreateUserCommand(Message\MessageRecipient $recipient)
    {
        $command = new CreateDiamanteUserCommand();
        $command->email     = $recipient->getEmail();
        $command->username  = $recipient->getEmail();
        $command->firstName = $recipient->getFirstName();
        $command->lastName  = $recipient->getLastName();

        return $command;
    }
}
