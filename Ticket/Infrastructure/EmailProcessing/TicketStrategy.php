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
namespace Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\EmailProcessing;

use Eltrino\DiamanteDeskBundle\Ticket\Model\EmailProcessing\Services\MessageReferenceService;
use Eltrino\DiamanteDeskBundle\Branch\Api\EmailProcessing\BranchEmailConfigurationService;
use Eltrino\EmailProcessingBundle\Model\Processing\Strategy;
use Eltrino\EmailProcessingBundle\Model\Message;
use Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings;

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
     * @var SystemSettings
     */
    private $emailProcessingSettings;

    /**
     * @param MessageReferenceService $messageReferenceService
     * @param BranchEmailConfigurationService $branchEmailConfigurationService
     * @param SystemSettings $settings
     */
    public function __construct(MessageReferenceService $messageReferenceService,
                                BranchEmailConfigurationService $branchEmailConfigurationService,
                                SystemSettings $settings)
    {
        $this->messageReferenceService         = $messageReferenceService;
        $this->branchEmailConfigurationService = $branchEmailConfigurationService;
        $this->emailProcessingSettings         = $settings;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $reporterId = 1;
        $assigneeId = 1;

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
            $branchId = $this->getAppropriateBranch($message->getFrom(), $message->getTo());
            $this->messageReferenceService->createTicket($message->getMessageId(), $branchId, $message->getSubject(),
                $message->getContent(), $reporterId, $assigneeId, null, null, $attachments);
        } else {
            $this->messageReferenceService->createCommentForTicket($message->getContent(), $reporterId,
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