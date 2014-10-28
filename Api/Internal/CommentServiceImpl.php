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
namespace Diamante\DeskBundle\Api\Internal;

use Diamante\DeskBundle\Api\CommentService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Ticket\CommentFactory;
use Diamante\DeskBundle\Model\Ticket\AttachmentService;
use Diamante\DeskBundle\Model\Shared\UserService;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\DeskBundle\Api\Command\RetrieveCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CommentServiceImpl implements CommentService
{
    /**
     * @var Repository
     */
    private $ticketRepository;

    /**
     * @var Repository
     */
    private $commentRepository;

    /**
     * @var CommentFactory
     */
    private $commentFactory;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var AttachmentService
     */
    private $attachmentService;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(
        Repository $ticketRepository,
        Repository $commentRepository,
        CommentFactory $commentFactory,
        UserService $userService,
        AttachmentService $attachmentService,
        SecurityFacade $securityFacade,
        EventDispatcher $dispatcher
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->userService = $userService;
        $this->attachmentService = $attachmentService;
        $this->securityFacade = $securityFacade;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Load Comment by given comment id
     * @param int $commentId
     * @return \Diamante\DeskBundle\Model\Ticket\Comment
     */
    public function loadComment($commentId)
    {
        return $this->loadCommentBy($commentId);
    }

    /**
     * @param $commentId
     * @return \Diamante\DeskBundle\Model\Ticket\Comment
     */
    private function loadCommentBy($commentId)
    {
        $comment = $this->commentRepository->get($commentId);
        if (is_null($comment)) {
            throw new \RuntimeException('Comment loading failed, comment not found.');
        }
        return $comment;
    }

    private function loadTicketBy($ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }
        return $ticket;
    }

    /**
     * Post Comment for Ticket
     * @param Command\EditCommentCommand $command
     * @return void
     */
    public function postNewCommentForTicket(Command\EditCommentCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Comment');

        /**
         * @var $ticket \Diamante\DeskBundle\Model\Ticket\Ticket
         */
        $ticket = $this->loadTicketBy($command->ticket);

        $author = $this->userService->getUserById($command->author);

        $comment = $this->commentFactory->create($command->content, $ticket, $author);

        if ($command->attachmentsInput) {
            $this->attachmentService->createAttachmentsForItHolder($command->attachmentsInput, $comment);
        }

        $ticket->postNewComment($comment);

        $this->ticketRepository->store($ticket);
    }

    /**
     * Retrieves Comment Attachment
     * @param RetrieveCommentAttachmentCommand $command
     * @return Attachment
     */
    public function getCommentAttachment(RetrieveCommentAttachmentCommand $command)
    {
        $comment = $this->loadCommentBy($command->commentId);

        $this->isGranted('VIEW', $comment);

        $attachment = $comment->getAttachment($command->attachmentId);
        if (is_null($attachment)) {
            throw new \RuntimeException('Attachment loading failed. Comment has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Update Ticket Comment content
     * @param Command\EditCommentCommand $command
     * @return void
     */
    public function updateTicketComment(Command\EditCommentCommand $command)
    {
        $comment = $this->loadCommentBy($command->id);

        $this->isGranted('EDIT', $comment);

        $comment->updateContent($command->content);
        if ($command->attachmentsInput) {
            $this->attachmentService->createAttachmentsForItHolder($command->attachmentsInput, $comment);
        }
        $this->commentRepository->store($comment);

        $changes = $comment->getRecordedEvents();

        //$dispatcher->addSubscriber() ....

        foreach ($changes as $change) {
            $this->dispatcher->dispatch($change->getEventName, $change);
        }
    }

    /**
     * Delete Ticket Comment
     * @param integer $commentId
     */
    public function deleteTicketComment($commentId)
    {
        $comment = $this->loadCommentBy($commentId);

        $this->isGranted('DELETE', $comment);

        $this->commentRepository->remove($comment);
    }

    /**
     * Remove Attachment from Comment
     * @param RemoveCommentAttachmentCommand $command
     * @return void
     * @throws \RuntimeException if Comment does not exists or Comment has no particular attachment
     */
    public function removeAttachmentFromComment(RemoveCommentAttachmentCommand $command)
    {
        $comment = $this->loadCommentBy($command->commentId);

        $this->isGranted('EDIT', $comment);

        $attachment = $comment->getAttachment($command->attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Comment has no such attachment.');
        }
        $this->attachmentService->removeAttachmentFromItHolder($attachment);
        $comment->removeAttachment($attachment);
        $this->commentRepository->store($comment);
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param $operation
     * @param $entity
     * @throws \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     */
    private function isGranted($operation, $entity)
    {
        if (!$this->securityFacade->isGranted($operation, $entity)) {
            throw new ForbiddenException("Not enough permissions.");
        }
    }
}
