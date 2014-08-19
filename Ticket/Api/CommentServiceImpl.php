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
use Eltrino\DiamanteDeskBundle\Form\Command\EditCommentCommand;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\CommentFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService;
use Eltrino\DiamanteDeskBundle\Ticket\Model\CommentRepository;
use Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

class CommentServiceImpl implements CommentService
{
    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var CommentRepository
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
        TicketRepository $ticketRepository,
        CommentRepository $commentRepository,
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
     * Post Comment for Ticket
     * @param string $content
     * @param integer $ticketId
     * @param integer $authorId
     * @param array $attachmentsInput array of AttachmentInput DTOs
     * @return void
     */
    public function postNewCommentForTicket($content, $ticketId, $authorId, array $attachmentsInput = null)
    {
        if (!$this->securityFacade->isGranted('CREATE', 'Entity:EltrinoDiamanteDeskBundle:Comment')) {
            throw new ForbiddenException("Not enough permissions.");
        }

        $ticket = $this->ticketRepository->get($ticketId);

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $author = $this->userService->getUserById($authorId);

        $comment = $this->commentFactory->create($content, $ticket, $author);

        if ($attachmentsInput) {
            $this->attachmentService->createAttachmentsForItHolder($attachmentsInput, $comment);
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
        $comment = $this->commentRepository->get($commentId);
        if (is_null($comment)) {
            throw new \RuntimeException('Comment loading failed, comment not found.');
        }
        $attachment = $comment->getAttachment($attachmentId);
        if (is_null($attachment)) {
            throw new \RuntimeException('Attachment loading failed. Comment has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Update Ticket Comment content
     * @param integer $commentId
     * @param string $content
     * @param array $attachmentsInput array of AttachmentInput DTOs
     */
    public function updateTicketComment($commentId, $content, array $attachmentsInput = null)
    {
        $comment = $this->commentRepository->get($commentId);

        if (is_null($comment)) {
            throw new \RuntimeException('Comment loading failed, comment not found.');
        }

        if (!$this->securityFacade->isGranted('EDIT', $comment)) {
            throw new ForbiddenException("Not enough permissions.");
        }

        $comment->updateContent($content);
        if ($attachmentsInput) {
            $this->attachmentService->createAttachmentsForItHolder($attachmentsInput, $comment);
        }
        $this->commentRepository->store($comment);
    }

    /**
     * Delete Ticket Comment
     * @param integer $commentId
     */
    public function deleteTicketComment($commentId)
    {
        $comment = $this->commentRepository->get($commentId);

        if (is_null($comment)) {
            throw new \RuntimeException('Comment loading failed, comment not found.');
        }

        if (!$this->securityFacade->isGranted('DELETE', $comment)) {
            throw new ForbiddenException("Not enough permissions.");
        }

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
        $comment = $this->commentRepository->get($commentId);

        if (is_null($comment)) {
            throw new \RuntimeException('Comment loading failed, comment not found.');
        }

        if (!$this->securityFacade->isGranted('EDIT', $comment)) {
            throw new ForbiddenException("Not enough permissions.");
        }

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
}
