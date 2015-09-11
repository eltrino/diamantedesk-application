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
namespace Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services;

use Diamante\DeskBundle\Api\Command\CommentCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\CommentService;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Api\TicketService;
use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\EmailProcessingBundle\Infrastructure\Message\Attachment;
use Diamante\UserBundle\Model\User;
use Symfony\Bridge\Monolog\Logger;

class MessageReferenceServiceImpl implements MessageReferenceService
{
    const DELIMITER_LINE = '[[ Please reply above this line ]]';
    const EMPTY_SUBJECT_PLACEHOLDER = '[No Subject]';

    /**
     * @var MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var TicketService
     */
    private $ticketService;

    /**
     * @var CommentService
     */
    private $commentService;

    /**
     * @param MessageReferenceRepository $messageReferenceRepository
     * @param Logger $logger
     * @param TicketService $ticketService
     * @param CommentService $commentService
     */
    public function __construct(
        MessageReferenceRepository $messageReferenceRepository,
        Logger  $logger,
        TicketService $ticketService,
        CommentService $commentService
    )
    {
        $this->messageReferenceRepository  = $messageReferenceRepository;
        $this->logger                      = $logger;
        $this->ticketService               = $ticketService;
        $this->commentService              = $commentService;
    }

    /**
     * Creates Ticket and Message Reference fot it
     *
     * @param $messageId
     * @param $branchId
     * @param $subject
     * @param $description
     * @param $reporter
     * @param $assigneeId
     * @param array|null $attachments
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($messageId, $branchId, $subject, $description, $reporter, $assigneeId,
                                 array $attachments = null, $priority = 'medium', $status = 'new')
    {
        if (empty($subject)) {
            $subject = self::EMPTY_SUBJECT_PLACEHOLDER;
        }

        $command = new CreateTicketCommand();
        $command->subject           = $subject;
        $command->description       = $description;
        $command->branch            = $branchId;
        $command->reporter          = $reporter;
        $command->assignee          = $assigneeId;
        $command->source            = Source::EMAIL;
        $command->attachmentsInput  = $this->convertAttachments($attachments);
        $command->priority          = $priority;
        $command->status            = $status;

        $ticket = $this->ticketService->createTicket($command);
        $this->createMessageReference($messageId, $ticket);

        return $ticket;
    }

    /**
     * Creates Comment for Ticket
     *
     * @param $content
     * @param $authorId
     * @param $messageId
     * @param array|null $attachments
     * @return Ticket|null
     */
    public function createCommentForTicket($content, $authorId, $messageId, array $attachments = null)
    {
        if (empty($content)) {
            return null;
        }

        $reference = $this->messageReferenceRepository
            ->getReferenceByMessageId($messageId);

        if (is_null($reference)) {
            $this->logger->error(sprintf('Ticket not found for message: %s', $messageId));
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $ticket = $reference->getTicket();

        $command = new CommentCommand();
        $command->ticket            = $ticket->getId();
        $command->content           = $content;
        $command->author            = User::fromString($authorId);
        $command->ticketStatus      = $ticket->getStatus();
        $command->attachmentsInput  = $this->convertAttachments($attachments);

        $this->commentService->postNewCommentForTicket($command);

        return $ticket;
    }

    /**
     * Create Message Reference
     *
     * @param $messageId
     * @param Ticket $ticket
     */
    private function createMessageReference($messageId, $ticket)
    {
        $messageReference = new MessageReference($messageId, $ticket);
        $this->messageReferenceRepository->store($messageReference);
    }

    /**
     * @param Attachment[]|null $attachments
     * @return AttachmentInput[]|null
     */
    private function convertAttachments(array $attachments = null)
    {
        if (is_null($attachments)) {
            return null;
        }

        $result = null;

        foreach ($attachments as $attachment) {
            if ($attachment instanceof Attachment) {
                $input = new AttachmentInput();
                $input->setFilename($attachment->getName());
                $input->setContent($attachment->getContent());
                $result[] = $input;
            }
        }

        return $result;
    }
}
