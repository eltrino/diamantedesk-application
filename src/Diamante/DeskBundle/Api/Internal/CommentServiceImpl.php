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
use Diamante\DeskBundle\Model\Attachment\Exception\AttachmentNotFoundException;
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\Model\Shared\FilterableRepository;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Ticket\CommentFactory;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\Ticket\Exception\CommentNotFoundException;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Api\Command\RetrieveCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Comment;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CommentServiceImpl implements CommentService
{
    use Shared\AttachmentTrait;
    use Shared\WorkflowTrait;

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
     * @var AttachmentManager
     */
    private $attachmentManager;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @param Registry $doctrineRegistry
     * @param Repository $ticketRepository
     * @param Repository $commentRepository
     * @param CommentFactory $commentFactory
     * @param UserService $userService
     * @param AttachmentManager $attachmentManager
     * @param AuthorizationService $authorizationService
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Registry $doctrineRegistry,
        Repository $ticketRepository,
        Repository $commentRepository,
        CommentFactory $commentFactory,
        UserService $userService,
        AttachmentManager $attachmentManager,
        AuthorizationService $authorizationService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->userService = $userService;
        $this->attachmentManager = $attachmentManager;
        $this->authorizationService = $authorizationService;
        $this->registry = $doctrineRegistry;
        $this->dispatcher = $eventDispatcher;
    }

    /**
     * Load Comment by given comment id
     * @param int $id
     * @return \Diamante\DeskBundle\Entity\Comment
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     */
    public function loadComment($id)
    {
        $comment = $this->loadCommentBy($id);
        $this->isGranted('VIEW', $comment);

        return $comment;
    }

    /**
     * @param $commentId
     * @return Comment
     *
     * @throws CommentNotFoundException
     */
    private function loadCommentBy($commentId)
    {
        $comment = $this->commentRepository->get($commentId);
        if (is_null($comment)) {
            throw new CommentNotFoundException();
        }
        return $comment;
    }

    /**
     * @param int $ticketId
     * @return Ticket
     *
     * @throws TicketNotFoundException
     */
    private function loadTicketBy($ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new TicketNotFoundException();
        }
        return $ticket;
    }

    /**
     * Post Comment for Ticket
     * @param Command\CommentCommand $command
     * @return Comment
     *
     * @throws ForbiddenException
     * @throws TicketNotFoundException
     */
    public function postNewCommentForTicket(Command\CommentCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Comment');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        /**
         * @var $ticket \Diamante\DeskBundle\Model\Ticket\Ticket
         */
        $ticket = $this->loadTicketBy($command->ticket);
        $author = User::fromString($command->author);
        $comment = $this->commentFactory->create($command->content, $ticket, $author, $command->private);

        $this->createAttachments($command, $comment);
        $ticket->updateStatus(new Status($command->ticketStatus));
        $ticket->postNewComment($comment);
        $this->ticketRepository->store($ticket);

        $this->dispatchWorkflowEvent(
            $this->registry,
            $this->dispatcher,
            $comment
        );

        return $comment;
    }

    /**
     * Retrieves comment attachments
     * @param $commentId
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     */
    public function listCommentAttachment($commentId)
    {
        $comment = $this->loadCommentBy($commentId);
        $this->isGranted('VIEW', $comment);
        return $comment->getAttachments();
    }

    /**
     * Retrieves Comment Attachment
     * @param RetrieveCommentAttachmentCommand $command
     * @return Attachment
     *
     * @throws ForbiddenException
     * @throws AttachmentNotFoundException
     * @throws CommentNotFoundException
     */
    public function getCommentAttachment(RetrieveCommentAttachmentCommand $command)
    {
        $comment = $this->loadCommentBy($command->commentId);

        $this->isGranted('VIEW', $comment);

        $attachment = $comment->getAttachment($command->attachmentId);
        if (!$attachment) {
            throw new AttachmentNotFoundException();
        }
        return $attachment;
    }

    /**
     * Add Attachments to Comment
     * @param Command\AddCommentAttachmentCommand $command
     * @param boolean $flush
     * @return array
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     */
    public function addCommentAttachment(Command\AddCommentAttachmentCommand $command, $flush = false)
    {
        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $comment = $this->loadCommentBy($command->commentId);

        $this->isGranted('EDIT', $comment);

        $attachments = $this->createAttachments($command, $comment);
        $this->registry->getManager()->persist($comment);

        if (true === $flush) {
            $this->registry->getManager()->flush();
        }

        return $attachments;
    }

    /**
     * Update Ticket Comment content
     * @param Command\CommentCommand $command
     * @param boolean $flush
     * @return void
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     */
    public function updateTicketComment(Command\CommentCommand $command, $flush = false)
    {
        $comment = $this->loadCommentBy($command->id);

        $this->isGranted('EDIT', $comment);

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $comment->updateContent($command->content);
        $comment->setPrivate($command->private);

        $this->createAttachments($command, $comment);

        $this->registry->getManager()->persist($comment);
        $ticket = $comment->getTicket();
        $this->updateTicketStatus($ticket, $command);

        $this->registry->getManager()->flush();

        $this->dispatchWorkflowEvent(
            $this->registry,
            $this->dispatcher,
            $comment
        );
    }

    /**
     * Update certain properties of the Comment
     * @param Command\UpdateCommentCommand $command
     * @param boolean $flush
     * @return Comment
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     */
    public function updateCommentContentAndTicketStatus(Command\UpdateCommentCommand $command, $flush = false)
    {
        $comment = $this->loadCommentBy($command->id);

        $this->isGranted('EDIT', $comment);

        $comment->updateContent($command->content);

        $ticket = $comment->getTicket();
        $this->updateTicketStatus($ticket, $command);

        if (true === $flush) {
            $this->registry->getManager()->flush();
        }

        $this->dispatchWorkflowEvent(
            $this->registry,
            $this->dispatcher,
            $comment
        );

        return $comment;
    }

    /**
     * Delete Ticket Comment
     * @param integer $commentId
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     */
    public function deleteTicketComment($commentId)
    {
        $comment = $this->loadCommentBy($commentId);
        $this->isGranted('DELETE', $comment);

        $comment->delete();

        $this->registry->getManager()->remove($comment);
        foreach ($comment->getAttachments() as $attachment) {
            $this->attachmentManager->deleteAttachment($attachment);
        }

        $this->registry->getManager()->flush();

        $this->dispatchWorkflowEvent(
            $this->registry,
            $this->dispatcher,
            $comment
        );
    }

    /**
     * Remove Attachment from Comment
     * @param RemoveCommentAttachmentCommand $command
     * @param boolean $flush
     * @return void
     *
     * @throws ForbiddenException
     * @throws CommentNotFoundException
     * @throws AttachmentNotFoundException
     */
    public function removeAttachmentFromComment(RemoveCommentAttachmentCommand $command, $flush = false)
    {
        $comment = $this->loadCommentBy($command->commentId);

        $this->isGranted('EDIT', $comment);

        $attachment = $comment->getAttachment($command->attachmentId);
        if (empty($attachment)) {
            throw new AttachmentNotFoundException();
        }
        $comment->removeAttachment($attachment);
        $this->registry->getManager()->persist($comment);
        $this->attachmentManager->deleteAttachment($attachment);

        if (true === $flush) {
            $this->registry->getManager()->flush();
        }
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param string $operation
     * @param Comment|string $entity
     * @throws ForbiddenException
     */
    private function isGranted($operation, $entity)
    {
        // User should have ability to view all comments (except private)
        // if he is an owner of a ticket
        if ($operation === 'VIEW' && is_object($entity)) {
            if ($this->authorizationService->getLoggedUser()) {
                $loggedUser = $this->authorizationService->getLoggedUser();

                if($loggedUser instanceof ApiUser) {
                    $loggedUser = $this->userService->getUserFromApiUser($loggedUser);
                }

                /** @var User $reporter */
                $reporter = $entity->getTicket()->getReporter();
                if ($loggedUser && $reporter && $loggedUser->getId() == $reporter->getId()) {
                    return;
                }
            }
        }

        if (!$this->authorizationService->isActionPermitted($operation, $entity)) {
            throw new ForbiddenException("Not enough permissions.");
        }
    }

    /**
     * @return FilterableRepository
     */
    protected function getCommentsRepository()
    {
        return $this->commentRepository;
    }

    /**
     * @param Ticket $ticket
     * @param $command
     */
    protected function updateTicketStatus(Ticket $ticket, $command)
    {
        $status = new Status($command->ticketStatus);
        if (false === $ticket->getStatus()->equals($status)) {
            $ticket->updateStatus($status);
            $this->registry->getManager()->persist($ticket);
        }
    }
}
