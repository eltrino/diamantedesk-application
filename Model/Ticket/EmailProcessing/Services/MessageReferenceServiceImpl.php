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

use Diamante\DeskBundle\Model\Attachment\AttachmentFactory;
use Diamante\DeskBundle\Model\Attachment\AttachmentHolder;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\Services\FileStorageService;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Shared\UserService;
use Diamante\DeskBundle\Model\Ticket\CommentFactory;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketFactory;
use Diamante\DeskBundle\Model\Ticket\Source;

use Diamante\DeskBundle\Api\Command\CreateCommentFromMessageCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketFromMessageCommand;

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
     * @var Repository
     */
    private $branchRepository;

    /**
     * @var Repository
     */
    private $attachmentRepository;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var CommentFactory
     */
    private $commentFactory;

    /**
     * @var AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var FileStorageService
     */
    private $fileStorageService;

    public function __construct(
        MessageReferenceRepository $messageReferenceRepository,
        Repository $ticketRepository,
        Repository $branchRepository,
        Repository $attachmentRepository,
        TicketFactory $ticketFactory,
        CommentFactory $commentFactory,
        AttachmentFactory $attachmentFactory,
        UserService $userService,
        FileStorageService $fileStorageService
    )
    {
        $this->messageReferenceRepository = $messageReferenceRepository;
        $this->ticketRepository           = $ticketRepository;
        $this->branchRepository           = $branchRepository;
        $this->attachmentRepository       = $attachmentRepository;
        $this->ticketFactory              = $ticketFactory;
        $this->commentFactory             = $commentFactory;
        $this->attachmentFactory          = $attachmentFactory;
        $this->userService                = $userService;
        $this->fileStorageService         = $fileStorageService;
    }

    /**
     * Creates Ticket and Message Reference fot it
     * @param CreateTicketFromMessageCommand $command
     * @return Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket(CreateTicketFromMessageCommand $command)
    {
        $branch = $this->branchRepository->get($command->branchId);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed, branch not found.');
        }

        $reporter = $this->userService->getUserById($command->reporterId);
        if (is_null($reporter)) {
            throw new \RuntimeException('Reporter loading failed, reporter not found.');
        }

        $assignee = $this->userService->getUserById($command->assigneeId);
        if (is_null($assignee)) {
            throw new \RuntimeException('Assignee validation failed, assignee not found.');
        }

        $ticket = $this->ticketFactory
            ->create($command->subject,
                $command->description,
                $branch,
                $reporter,
                $assignee,
                $command->priority,
                Source::EMAIL,
                $command->status);

        if ($command->attachments) {
            $this->createAttachments($command->attachments, $ticket);
        }
        $this->ticketRepository->store($ticket);
        $this->createMessageReference($command->messageId, $ticket);

        return $ticket;
    }

    /**
     * @param array $attachments
     * @param AttachmentHolder $attachmentHolder
     */
    private function createAttachments(array $attachments, AttachmentHolder $attachmentHolder)
    {
        $filenamePrefix = $this->exposeFilenamePrefixFrom($attachmentHolder);

        foreach ($attachments as $attachment) {
            try {
                $path = $this->fileStorageService->upload($filenamePrefix . '/' . $attachment->getName(), $attachment->getContent());

                $file = new File($path);

                $ticketAttachment = $this->attachmentFactory->create($file);

                $attachmentHolder->addAttachment($ticketAttachment);
                $this->attachmentRepository->store($ticketAttachment);
            } catch (\RuntimeException $e) {
                /**
                 * @todo logging
                 */
                throw $e;
            }
        }
    }

    /**
     * @param AttachmentHolder $attachmentHolder
     * @return string
     */
    private function exposeFilenamePrefixFrom(AttachmentHolder $attachmentHolder)
    {
        $parts = explode("\\", get_class($attachmentHolder));
        $prefix = strtolower(array_pop($parts));
        return $prefix;
    }

    /**
     * Creates Comment for Ticket
     * @param CreateCommentFromMessageCommand $command
     * @return void
     */
    public function createCommentForTicket(CreateCommentFromMessageCommand $command)
    {
        $ticket = $this->messageReferenceRepository
            ->getReferenceByMessageId($command->messageId)
            ->getTicket();

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $author = $this->userService->getUserById($command->authorId);
        $comment = $this->commentFactory->create($command->content, $ticket, $author);

        if ($command->attachments) {
            $this->createAttachments($command->attachments, $comment);
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
