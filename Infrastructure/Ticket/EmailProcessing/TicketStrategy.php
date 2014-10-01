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

use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\EmailProcessingBundle\Model\Processing\Strategy;
use Diamante\DeskBundle\Api\Command\CreateCommentFromMessageCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketFromMessageCommand;

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
            $createTicketFromMessageCommand = new CreateTicketFromMessageCommand();
            $createTicketFromMessageCommand->assigneeId  = $assigneeId;
            $createTicketFromMessageCommand->branchId    = $branchId;
            $createTicketFromMessageCommand->reporterId  = $reporterId;
            $createTicketFromMessageCommand->messageId   = $message->getMessageId();
            $createTicketFromMessageCommand->subject     = $message->getSubject();
            $createTicketFromMessageCommand->description = $message->getContent();
            $createTicketFromMessageCommand->attachments = $attachments;

            $this->messageReferenceServiceImpl->createTicket($createTicketFromMessageCommand);
        } else {
            $createCommentFromMessageCommand = new CreateCommentFromMessageCommand();
            $createCommentFromMessageCommand->authorId  = $reporterId;
            $createCommentFromMessageCommand->content   = $message->getContent();
            $createCommentFromMessageCommand->messageId = $message->getReference();
            $createCommentFromMessageCommand->attachments = $attachments;

            $this->messageReferenceServiceImpl->createCommentForTicket($createCommentFromMessageCommand);
        }
    }
}
