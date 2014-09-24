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
namespace Eltrino\DiamanteDeskBundle\Ticket\Api;

use Eltrino\DiamanteDeskBundle\Entity\Comment;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Form\Command\CreateCommentCommand;
use Eltrino\DiamanteDeskBundle\Model\Shared\Repository;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Command\EditCommentCommand;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\CommentFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

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
     * @var Internal\UserService
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

    public function __construct(
        Repository $ticketRepository,
        Repository $commentRepository,
        CommentFactory $commentFactory,
        UserService $userService,
        AttachmentService $attachmentService,
        SecurityFacade $securityFacade
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->userService = $userService;
        $this->attachmentService = $attachmentService;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Load Comment by given comment id
     * @param int $commentId
     * @return \Eltrino\DiamanteDeskBundle\Ticket\Model\Comment
     */
    public function loadComment($commentId)
    {
        return $this->loadCommentBy($commentId);
    }

    /**
     * @param $commentId
     * @return \Eltrino\DiamanteDeskBundle\Ticket\Model\Comment
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
     * @param EditCommentCommand $command
     * @return void
     */
    public function postNewCommentForTicket(EditCommentCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:EltrinoDiamanteDeskBundle:Comment');

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
     * @param integer $commentId
     * @param integer $attachmentId
     * @return Attachment
     */
    public function getCommentAttachment($commentId, $attachmentId)
    {
        $comment = $this->loadCommentBy($commentId);

        $this->isGranted('VIEW', $comment);

        $attachment = $comment->getAttachment($attachmentId);
        if (is_null($attachment)) {
            throw new \RuntimeException('Attachment loading failed. Comment has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Update Ticket Comment content
     * @param EditCommentCommand $command
     * @return void
     */
    public function updateTicketComment(EditCommentCommand $command)
    {
        $comment = $this->loadCommentBy($command->id);

        $this->isGranted('EDIT', $comment);

        $comment->updateContent($command->content);
        if ($command->attachmentsInput) {
            $this->attachmentService->createAttachmentsForItHolder($command->attachmentsInput, $comment);
        }
        $this->commentRepository->store($comment);
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
     * @param $commentId
     * @param $attachmentId
     * @return void
     * @throws \RuntimeException if Comment does not exists or Comment has no particular attachment
     */
    public function removeAttachmentFromComment($commentId, $attachmentId)
    {
        $comment = $this->loadCommentBy($commentId);

        $this->isGranted('EDIT', $comment);

        $attachment = $comment->getAttachment($attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Comment has no such attachment.');
        }
        $this->attachmentService->removeAttachmentFromItHolder($attachment);
        $comment->removeAttachment($attachment);
        $this->commentRepository->store($comment);
    }

    public static function create(
        \Doctrine\ORM\EntityManager $em,
        UserService $userService,
        AttachmentService $attachmentService,
        SecurityFacade $securityFacade
    ) {
        return new CommentServiceImpl(
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Ticket'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Comment'),
            new CommentFactory(),
            $userService,
            $attachmentService,
            $securityFacade
        );
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
