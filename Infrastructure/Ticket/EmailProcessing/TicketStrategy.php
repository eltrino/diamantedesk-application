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

use Diamante\DeskBundle\Infrastructure\Shared\Adapter\DiamanteContactService;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceService;
use Diamante\DeskBundle\Api\BranchEmailConfigurationService;
use Diamante\DeskBundle\Model\User\DiamanteUserFactory;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\DeskBundle\Model\User\User;
use Diamante\EmailProcessingBundle\Model\Mail\SystemSettings;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Processing\Strategy;

class TicketStrategy implements Strategy
{
    /**
     * @var MessageReferenceService
     */
    private $messageReferenceService;

    /**
     * @var BranchEmailConfigurationService
     */
    private $branchEmailConfigurationService;

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
     * @var DiamanteContactService
     */
    private $diamanteContactService;

    /**
     * @param MessageReferenceService $messageReferenceService
     * @param BranchEmailConfigurationService $branchEmailConfigurationService
     * @param DiamanteUserRepository $diamanteUserRepository
     * @param DiamanteUserFactory $diamanteUserFactory
     * @param SystemSettings $settings
     * @param DiamanteContactService $diamanteContactService
     */
    public function __construct(MessageReferenceService $messageReferenceService,
                                BranchEmailConfigurationService $branchEmailConfigurationService,
                                DiamanteUserRepository $diamanteUserRepository,
                                DiamanteUserFactory $diamanteUserFactory,
                                SystemSettings $settings,
                                DiamanteContactService $diamanteContactService)
    {
        $this->messageReferenceService         = $messageReferenceService;
        $this->branchEmailConfigurationService = $branchEmailConfigurationService;
        $this->diamanteUserRepository          = $diamanteUserRepository;
        $this->diamanteUserFactory             = $diamanteUserFactory;
        $this->emailProcessingSettings         = $settings;
        $this->diamanteContactService          = $diamanteContactService;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $assigneeId = 1;

        $email = $message->getFrom()->getEmail();
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($email);
        $type = User::TYPE_DIAMANTE;

        if (is_null($diamanteUser)) {
            $contact = $this->diamanteContactService->findEmailOwner($email);
            $sender = $message->getFrom();
            $diamanteUser = $this->diamanteUserFactory->create($email, $email, $contact, $sender->getFirstName(), $sender->getLastName());

            $this->diamanteUserRepository->store($diamanteUser);
            $type = User::TYPE_DIAMANTE;
        }

        $reporterId = $diamanteUser->getId();

        $reporter = new User($reporterId, $type);

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
            $branchId = $this->getAppropriateBranch($message->getFrom()->getEmail(), $message->getTo());
            $this->messageReferenceService->createTicket($message->getMessageId(), $branchId, $message->getSubject(),
                $message->getContent(), $reporter, $assigneeId, $attachments);
        } else {
            $this->messageReferenceService->createCommentForTicket($message->getContent(), $reporter,
                $message->getReference(), $attachments);
        }
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
}
