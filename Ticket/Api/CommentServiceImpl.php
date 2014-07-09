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

    public function __construct(
        TicketRepository $ticketRepository,
        CommentRepository $commentRepository,
        CommentFactory $commentFactory,
        UserService $userService,
        AttachmentService $attachmentService
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->userService = $userService;
        $this->attachmentService = $attachmentService;
    }

    /**
     * Post Comment for Ticket
     * @param string $content
     * @param integer $ticketId
     * @param integer $authorId
     * @param \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto
     * @return void
     */
    public function postNewCommentForTicket($content, $ticketId, $authorId, \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto = null)
    {
        $ticket = $this->ticketRepository->get($ticketId);

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket not found.');
        }

        $author = $this->userService->getUserById($authorId);

        $comment = $this->commentFactory->create($content, $ticket, $author);

        if ($filesListDto) {
            $this->attachmentService->createAttachmentsForItHolder($filesListDto, $comment);
        }

        $ticket->postNewComment($comment);

        $this->ticketRepository->store($ticket);
    }

    public function getCommentAttachment($commentId, $attachmentId)
    {
        $comment = $this->commentRepository->get($commentId);
        if (is_null($comment)) {
            throw new \RuntimeException('Comment not found.');
        }
        $attachment = $comment->getAttachment($attachmentId);
        if (is_null($attachment)) {
            throw new \RuntimeException('Comment has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Update Ticket Comment content
     * @param integer $commentId
     * @param string $content
     * @param \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto
     */
    public function updateTicketComment($commentId, $content, \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto = null)
    {
        $comment = $this->commentRepository->get($commentId);
        if (is_null($comment)) {
            throw new \RuntimeException('Comment not found.');
        }
        $comment->updateContent($content);
        if ($filesListDto) {
            $this->attachmentService->createAttachmentsForItHolder($filesListDto, $comment);
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
            throw new \RuntimeException('Comment not found.');
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
        if (!$comment) {
            throw new \RuntimeException('Comment not found.');
        }
        $attachment = $comment->getAttachment($attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Comment has no such attachment.');
        }
        $this->attachmentService->removeAttachmentFromItHolder($attachment);
        $comment->removeAttachment($attachment);
        $this->commentRepository->store($comment);
    }

    public static function create(
        \Doctrine\ORM\EntityManager $em,
        UserService $userService,
        AttachmentService $attachmentService
    ) {
        return new CommentServiceImpl(
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Ticket'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Comment'),
            new CommentFactory(),
            $userService,
            $attachmentService
        );
    }
}
