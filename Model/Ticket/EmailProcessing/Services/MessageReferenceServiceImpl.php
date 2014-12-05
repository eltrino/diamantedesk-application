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

use Diamante\DeskBundle\Model\Attachment\AttachmentHolder;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Shared\UserService;
use Diamante\DeskBundle\Model\Ticket\CommentFactory;
use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketBuilder;
use Diamante\DeskBundle\Model\Ticket\TicketFactory;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\User\User;

class MessageReferenceServiceImpl implements MessageReferenceService
{
    /**
     * @var MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var Repository
     */
    private $ticketRepository;

    /**
     * @var TicketBuilder
     */
    private $ticketBuilder;

    /**
     * @var CommentFactory
     */
    private $commentFactory;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var AttachmentManager
     */
    private $attachmentManager;

    public function __construct(
        MessageReferenceRepository $messageReferenceRepository,
        Repository $ticketRepository,
        TicketBuilder $ticketBuilder,
        CommentFactory $commentFactory,
        UserService $userService,
        AttachmentManager $attachmentManager
    )
    {
        $this->messageReferenceRepository = $messageReferenceRepository;
        $this->ticketRepository           = $ticketRepository;
        $this->ticketBuilder              = $ticketBuilder;
        $this->commentFactory             = $commentFactory;
        $this->userService                = $userService;
        $this->attachmentManager          = $attachmentManager;
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
     * @param array $attachments
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($messageId, $branchId, $subject, $description, $reporter, $assigneeId,
                                 array $attachments = null)
    {
        $this->ticketBuilder
            ->setSubject($subject)
            ->setDescription($description)
            ->setBranchId($branchId)
            ->setReporter($reporter)
            ->setAssigneeId($assigneeId)
            ->setSource(Source::EMAIL);

        $ticket = $this->ticketBuilder->build();

        if ($attachments) {
            $this->createAttachments($attachments, $ticket);
        }
        $this->ticketRepository->store($ticket);
        $this->createMessageReference($messageId, $ticket);

        return $ticket;
    }

    /**
     * @param array $attachments
     * @param AttachmentHolder $attachmentHolder
     */
    private function createAttachments(array $attachments, AttachmentHolder $attachmentHolder)
    {
        foreach ($attachments as $attachment) {
            $this->attachmentManager
                ->createNewAttachment($attachment->getName(), $attachment->getContent(), $attachmentHolder);
        }
    }

    /**
     * Creates Comment for Ticket
     *
     * @param $content
     * @param $authorId
     * @param $messageId
     * @param array $attachments
     * @return void
     */
    public function createCommentForTicket($content, $authorId, $messageId, array $attachments = null)
    {
        $ticket = $this->messageReferenceRepository
            ->getReferenceByMessageId($messageId)
            ->getTicket();

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $author = User::fromString($authorId);
        $comment = $this->commentFactory->create($content, $ticket, $author);

        if ($attachments) {
            $this->createAttachments($attachments, $comment);
        }

        $ticket->postNewComment($comment);
        $this->ticketRepository->store($ticket);
    }

    /**
     * Create Message Reference
     *
     * @param $messageId
     * @param $ticket
     */
    private function createMessageReference($messageId, $ticket)
    {
        $messageReference = new MessageReference($messageId, $ticket);
        $this->messageReferenceRepository->store($messageReference);
    }
}
