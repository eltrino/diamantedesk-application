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
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Branch\Exception\BranchNotFoundException;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceService;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\EmailProcessingBundle\Model\Mail\SystemSettings;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Processing\Strategy;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\UserBundle\Entity\UserManager as OroUserManager;

class TicketStrategy implements Strategy
{

    const EMAIL_NOTIFIER_CONFIG_PATH = 'oro_notification.email_notification_sender_email';

    /**
     * @var MessageReferenceService
     */
    private $messageReferenceService;

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
     * @var UserService
     */
    private $diamanteUserService;

    /**
     * @var DoctrineGenericRepository
     */
    private $branchRepository;

    /**
     * @param MessageReferenceService $messageReferenceService
     * @param SystemSettings $settings
     * @param WatchersServiceImpl $watchersService
     * @param OroUserManager $oroUserManager
     * @param ConfigManager $configManager
     * @param UserService $diamanteUserService
     * @param DoctrineGenericRepository $branchRepository
     */
    public function __construct(MessageReferenceService $messageReferenceService,
                                SystemSettings $settings,
                                WatchersServiceImpl $watchersService,
                                OroUserManager $oroUserManager,
                                ConfigManager $configManager,
                                UserService $diamanteUserService,
                                DoctrineGenericRepository $branchRepository
    )
    {
        $this->messageReferenceService         = $messageReferenceService;
        $this->emailProcessingSettings         = $settings;
        $this->watchersService                 = $watchersService;
        $this->oroUserManager                  = $oroUserManager;
        $this->configManager                   = $configManager;
        $this->diamanteUserService             = $diamanteUserService;
        $this->branchRepository                = $branchRepository;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $diamanteUser = $this->diamanteUserService->getUserByEmail($message->getFrom()->getEmail());

        if (is_null($diamanteUser) || !$diamanteUser->isDiamanteUser()) {
            $id = $this->diamanteUserService->createDiamanteUser($this->prepareCreateUserCommand($message->getFrom()));
            $diamanteUser = new User($id, User::TYPE_DIAMANTE);
        }

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
                $defaultBranch = (int)$this->configManager->get('diamante_desk.default_branch');

                if (is_null($defaultBranch)) {
                    throw new \RuntimeException("Invalid configuration, default branch should be configured");
                }

                $branch = $this->getBranch($defaultBranch);

                $assigneeId = $branch->getDefaultAssigneeId() ? $branch->getDefaultAssigneeId() : null;

                $ticket = $this->messageReferenceService->createTicket($message, $defaultBranch,
                    (string)$diamanteUser, $assigneeId, $attachments);
        } else {
            $ticket = $this->messageReferenceService->createCommentForTicket($message->getContent(), (string)$diamanteUser,
                $message->getReference(), $attachments);
        }

        $this->processWatchers($message, $ticket);
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
                $id = $this->diamanteUserService->createDiamanteUser($this->prepareCreateUserCommand($recipient));
                $user = new User($id, User::TYPE_DIAMANTE);
            }

            $this->watchersService->addWatcher($ticket, $user);
        }
    }

    /**
     * @param int $id
     *
     * @return \Diamante\DeskBundle\Entity\Branch
     */
    private function getBranch($id) {
        $branch = $this->branchRepository->get($id);

        if (is_null($branch)) {
            throw new BranchNotFoundException();
        }

        return $branch;
    }

    /**
     * @param Message\Person $person
     * @return CreateDiamanteUserCommand
     */
    protected function prepareCreateUserCommand(Message\Person $person)
    {
        $command = new CreateDiamanteUserCommand();
        $command->email     = $person->getEmail();
        $command->username  = $person->getEmail();
        $command->firstName = $person->getFirstName();
        $command->lastName  = $person->getLastName();

        return $command;
    }
}
