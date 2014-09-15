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

use Eltrino\DiamanteDeskBundle\Ticket\Model\EmailProcessing\Services\MessageReferenceServiceImpl;
use Eltrino\EmailProcessingBundle\Model\Processing\Strategy;
use Eltrino\EmailProcessingBundle\Model\Message;

class TicketStrategy implements Strategy
{
    /**
     * @var MessageReferenceServiceImpl
     */
    private $messageReferenceServiceImpl;

    /**
     * @param MessageReferenceServiceImpl $messageReferenceServiceImpl
     */
    public function __construct(MessageReferenceServiceImpl $messageReferenceServiceImpl)
    {
        $this->messageReferenceServiceImpl = $messageReferenceServiceImpl;
    }

    /**
     * @param Message $message
     */
    public function process(Message $message)
    {
        $branchId   = 1;
        $reporterId = 1;
        $assigneeId = 1;

        $attachments = $message->getAttachments();

        if (!$message->getReference()) {
            $this->messageReferenceServiceImpl->createTicket($message->getMessageId(), $branchId, $message->getSubject(),
                $message->getContent(), $reporterId, $assigneeId, null, null, $attachments);
        } else {
            $this->messageReferenceServiceImpl->createCommentForTicket($message->getContent(), $reporterId,
                $message->getReference(), $attachments);
        }
    }
}