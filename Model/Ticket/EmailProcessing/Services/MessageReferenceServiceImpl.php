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
namespace Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\Services;

use Doctrine\ORM\EntityManager;

use Eltrino\DiamanteDeskBundle\Model\Attachment\AttachmentFactory;
use Eltrino\DiamanteDeskBundle\Model\Attachment\Services\FileStorageService;
use Eltrino\DiamanteDeskBundle\Model\Attachment\AttachmentHolder;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket;
use Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReference;
use Eltrino\DiamanteDeskBundle\Model\Attachment\File;
use Eltrino\DiamanteDeskBundle\Model\Shared\Repository;
use Eltrino\DiamanteDeskBundle\Model\Ticket\TicketFactory;
use Eltrino\DiamanteDeskBundle\Model\Ticket\CommentFactory;
use Eltrino\DiamanteDeskBundle\Model\Shared\UserService;
use Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Source;

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
     *
     * @param $messageId
     * @param $branchId
     * @param $subject
     * @param $description
     * @param $reporterId
     * @param $assigneeId
     * @param null $priority
     * @param null $status
     * @param array $attachments
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($messageId, $branchId, $subject, $description, $reporterId, $assigneeId,
             $priority = null, $status = null, array $attachments = null)
    {
        $branch = $this->branchRepository->get($branchId);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed, branch not found.');
        }

        $reporter = $this->userService->getUserById($reporterId);
        if (is_null($reporter)) {
            throw new \RuntimeException('Reporter loading failed, reporter not found.');
        }

        $assignee = $this->userService->getUserById($assigneeId);
        if (is_null($assignee)) {
            throw new \RuntimeException('Assignee validation failed, assignee not found.');
        }

        $ticket = $this->ticketFactory
            ->create($subject,
                $description,
                $branch,
                $reporter,
                $assignee,
                $priority,
                Source::EMAIL,
                $status);

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

        $author = $this->userService->getUserById($authorId);
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
