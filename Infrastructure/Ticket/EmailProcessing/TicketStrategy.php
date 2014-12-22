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

use Diamante\ApiBundle\Model\ApiUser\ApiUserFactory;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceService;
use Diamante\DeskBundle\Api\BranchEmailConfigurationService;
use Diamante\DeskBundle\Model\User\User;
use Diamante\EmailProcessingBundle\Model\Mail\SystemSettings;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Processing\Strategy;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;

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
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var ApiUserFactory
     */
    private $apiUserFactory;

    /**
     * @var SystemSettings
     */
    private $emailProcessingSettings;

    /**
     * @param MessageReferenceService $messageReferenceService
     * @param BranchEmailConfigurationService $branchEmailConfigurationService
     * @param ApiUserRepository $apiUserRepository
     * @param ApiUserFactory $apiUserFactory
     * @param SystemSettings $settings
     */
    public function __construct(MessageReferenceService $messageReferenceService,
                                BranchEmailConfigurationService $branchEmailConfigurationService,
                                ApiUserRepository $apiUserRepository,
                                ApiUserFactory $apiUserFactory,
                                SystemSettings $settings)
    {
        $this->messageReferenceService         = $messageReferenceService;
        $this->branchEmailConfigurationService = $branchEmailConfigurationService;
        $this->apiUserRepository               = $apiUserRepository;
        $this->apiUserFactory                  = $apiUserFactory;
        $this->emailProcessingSettings         = $settings;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $assigneeId = 1;

        $apiUser = $this->apiUserRepository->findUserByEmail($message->getFrom());

        if (is_null($apiUser)) {
            $apiUser = $this->apiUserFactory->create($message->getFrom(), $message->getFrom());
            $this->apiUserRepository->store($apiUser);
        }

        $reporterId = $apiUser->getId();

        $reporter = new User($reporterId, User::TYPE_DIAMANTE);

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
            $branchId = $this->getAppropriateBranch($message->getFrom(), $message->getTo());
            $this->messageReferenceService->createTicket($message->getMessageId(), $branchId, $message->getSubject(),
                $message->getContent(), $reporter, $assigneeId, null, null, $attachments);
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
