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

use Diamante\DeskBundle\Api\TicketService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketHistoryRepository;
use Diamante\DeskBundle\Model\Attachment\Exception\AttachmentNotFoundException;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketMovedException;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Ticket\TicketBuilder;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Diamante\DeskBundle\Entity\TicketHistory;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class TicketServiceImpl implements TicketService
{
    use Shared\AttachmentTrait;
    use Shared\WorkflowTrait;

    /**
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var Repository
     */
    private $branchRepository;

    /**
     * @var AttachmentManager
     */
    private $attachmentManager;

    /**
     * @var TicketBuilder
     */
    private $ticketBuilder;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var DoctrineTicketHistoryRepository
     */
    private $ticketHistoryRepository;

    /**
     * @var TagManager
     */
    private $tagManager;

    /**
     * @var OroUser|ApiUser
     */
    protected $loggedUser;

    /**
     * @param Registry $doctrineRegistry
     * @param TicketBuilder $ticketBuilder
     * @param AttachmentManager $attachmentManager
     * @param UserService $userService
     * @param AuthorizationService $authorizationService
     * @param EventDispatcher $dispatcher
     * @param TagManager $tagManager
     * @param SecurityFacade $securityFacade
     */
    public function __construct(Registry $doctrineRegistry,
                                TicketBuilder $ticketBuilder,
                                AttachmentManager $attachmentManager,
                                UserService $userService,
                                AuthorizationService $authorizationService,
                                EventDispatcher $dispatcher,
                                TagManager $tagManager,
                                SecurityFacade $securityFacade
    ) {
        $this->doctrineRegistry        = $doctrineRegistry;
        $this->ticketBuilder           = $ticketBuilder;
        $this->userService             = $userService;
        $this->attachmentManager       = $attachmentManager;
        $this->authorizationService    = $authorizationService;
        $this->dispatcher              = $dispatcher;
        $this->tagManager              = $tagManager;

        $this->ticketRepository        = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:Ticket');
        $this->branchRepository        = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:Branch');
        $this->ticketHistoryRepository = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:TicketHistory');
        $this->loggedUser              = $securityFacade->getLoggedUser();
    }

    /**
     * Load Ticket by given ticket id
     * @param int $id
     * @return Ticket
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function loadTicket($id)
    {
        $ticket = $this->loadTicketById($id);
        $this->loadTagging($ticket);

        $this->isGranted('VIEW', $ticket);
        return $ticket;
    }

    /**
     * Load Ticket by given Ticket Key
     * @param string $key
     * @return Ticket
     *
     * @throws ForbiddenException
     * @throws TicketNotFoundException
     */
    public function loadTicketByKey($key)
    {
        $ticketHistory = $this->ticketHistoryRepository->findOneByTicketKey($key);
        if ($ticketHistory) {
            $ticket = $this->ticketRepository->get($ticketHistory->getTicketId());
            $currentKey = (string)$ticket->getKey();
            throw new TicketMovedException($currentKey);
        } else {
            $ticketKey = TicketKey::from($key);
            $ticket = $this->loadTicketByTicketKey($ticketKey);
        }

        $this->loadTagging($ticket);

        $this->isGranted('VIEW', $ticket);

        return $ticket;
    }

    /**
     * @param TicketKey $ticketKey
     * @return Ticket
     *
     * @throws TicketNotFoundException
     */
    private function loadTicketByTicketKey(TicketKey $ticketKey)
    {
        $ticket = $this->ticketRepository
            ->getByTicketKey($ticketKey);
        if (is_null($ticket)) {
            throw new TicketNotFoundException('Ticket loading failed, ticket not found.');
        }

        $this->removePrivateComments($ticket);

        return $ticket;
    }

    /**
     * @param int $id
     * @return Ticket
     * @throws TicketNotFoundException if Ticket does not exists
     */
    private function loadTicketById($id)
    {
        /** @var Ticket $ticket */
        $ticket = $this->ticketRepository->get($id);
        if (is_null($ticket)) {
            throw new TicketNotFoundException('Ticket loading failed, ticket not found.');
        }

        $this->removePrivateComments($ticket);

        return $ticket;
    }

    /**
     * List Ticket attachments
     * @param int $id
     * @return \Doctrine\Common\Collections\ArrayCollection
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function listTicketAttachments($id)
    {
        $ticket = $this->loadTicket($id);
        $this->isGranted('VIEW', $ticket);
        return $ticket->getAttachments();
    }

    /**
     * Retrieves Ticket Attachment
     * @param Command\RetrieveTicketAttachmentCommand $command
     * @return \Diamante\DeskBundle\Entity\Attachment
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     * @throws AttachmentNotFoundException
     */
    public function getTicketAttachment(Command\RetrieveTicketAttachmentCommand $command)
    {
        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('VIEW', $ticket);

        $attachment = $ticket->getAttachment($command->attachmentId);
        if (empty($attachment)) {
            throw new AttachmentNotFoundException();
        }
        return $attachment;
    }

    /**
     * Adds Attachments for Ticket
     * @param Command\AddTicketAttachmentCommand $command
     * @return array
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function addAttachmentsForTicket(Command\AddTicketAttachmentCommand $command)
    {
        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        $attachments = $this->createAttachments($command, $ticket);

        $this->ticketRepository->store($ticket);

        return $attachments;
    }

    /**
     * Remove Attachment from Ticket
     * @param Command\RemoveTicketAttachmentCommand $command
     * @param boolean $flush
     * @return TicketKey
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     * @throws AttachmentNotFoundException
     */
    public function removeAttachmentFromTicket(Command\RemoveTicketAttachmentCommand $command, $flush = false)
    {
        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        $attachment = $ticket->getAttachment($command->attachmentId);
        if (!$attachment) {
            throw new AttachmentNotFoundException();
        }

        $ticket->removeAttachment($attachment);
        $this->doctrineRegistry->getManager()->persist($ticket);

        $this->attachmentManager->deleteAttachment($attachment);

        if (true === $flush) {
            $this->doctrineRegistry->getManager()->flush();
        }

        return $ticket->getKey();
    }

    /**
     * Create Ticket
     * @param Command\CreateTicketCommand $command
     * @return Ticket
     *
     * @throws ForbiddenException
     */
    public function createTicket(Command\CreateTicketCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Ticket');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $this->ticketBuilder
            ->setSubject($command->subject)
            ->setDescription($command->description)
            ->setBranchId($command->branch)
            ->setReporter($command->reporter)
            ->setAssignee($command->assignee)
            ->setPriority($command->priority)
            ->setSource($command->source)
            ->setStatus($command->status)
            ->setTags($command->tags);

        $ticket = $this->ticketBuilder->build();

        $this->createAttachments($command, $ticket);

        $this->doctrineRegistry->getManager()->persist($ticket);
        $this->doctrineRegistry->getManager()->flush();

        if ($this->loggedUser instanceof OroUser) {
            $this->tagManager->saveTagging($ticket);
            $ticket->setTags(null);
            $this->loadTagging($ticket);
        }

        $this->dispatchWorkflowEvent(
            $this->doctrineRegistry,
            $this->dispatcher,
            $ticket
        );

        return $ticket;
    }

    /**
     * Update Ticket
     *
     * @param Command\UpdateTicketCommand $command
     * @return Ticket
     *
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket(Command\UpdateTicketCommand $command)
    {
        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $ticket = $this->loadTicketById($command->id);

        $this->isGranted('EDIT', $ticket);

        $reporter = $ticket->getReporter();
        if ((string)$command->reporter !== (string)$reporter) {
            $reporter = $command->reporter;
        }

        $assignee = null;
        if ($command->assignee) {
            $assignee = $ticket->getAssignee();
            $currentAssigneeId = empty($assignee) ? null : $assignee->getId();

            if ($command->assignee != $currentAssigneeId) {
                $assignee = $this->userService->getByUser(new User((int)$command->assignee, User::TYPE_ORO));
            }
        }

        $ticket->update(
            $command->subject,
            $command->description,
            $reporter,
            new Priority($command->priority),
            new Status($command->status),
            new Source($command->source),
            $assignee,
            $command->tags
        );

        $this->createAttachments($command, $ticket);

        $this->doctrineRegistry->getManager()->persist($ticket);
        $this->doctrineRegistry->getManager()->flush();
        $this->tagManager->saveTagging($ticket);

        $ticket->setTags(null);
        $this->loadTagging($ticket);

        $this->dispatchWorkflowEvent(
            $this->doctrineRegistry,
            $this->dispatcher,
            $ticket
        );

        return $ticket;
    }

    /**
     * @param Command\UpdateStatusCommand $command
     * @return Ticket
     *
     * @throws \RuntimeException if unable to load required ticket
     */
    public function updateStatus(Command\UpdateStatusCommand $command)
    {
        $ticket = $this->loadTicketById($command->ticketId);

        $this->isAssigneeGranted($ticket);

        $ticket->updateStatus(new Status($command->status));
        $this->ticketRepository->store($ticket);

        $this->dispatchWorkflowEvent(
            $this->doctrineRegistry,
            $this->dispatcher,
            $ticket
        );

        return $ticket;
    }

    /**
     * @@param Command\MoveTicketCommand $command
     * @return void
     * @throws \RuntimeException if unable to load required ticket
     */
    public function moveTicket(Command\MoveTicketCommand $command)
    {
        $ticket = $this->loadTicketById($command->id);

        try {
            $this->ticketHistoryRepository->store(new TicketHistory($ticket->getId(), $ticket->getKey()));
            $ticket->move($command->branch);
            $this->doctrineRegistry->getManager()->persist($ticket);

            //Remove old key from history to prevent loop redirects
            if ($oldHistory = $this->ticketHistoryRepository->findOneByTicketKey($ticket->getKey())) {
                $this->doctrineRegistry->getManager()->remove($oldHistory);
            }

            $this->doctrineRegistry->getManager()->flush();

            $this->dispatchWorkflowEvent(
                $this->doctrineRegistry,
                $this->dispatcher,
                $ticket
            );

        } catch (\Exception $e) {
            throw new TicketMovedException($e->getMessage());
        }
    }

    /**
     * Assign Ticket to specified User
     * @param Command\AssigneeTicketCommand $command
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket(Command\AssigneeTicketCommand $command)
    {
        $ticket = $this->loadTicketById($command->id);

        $this->isAssigneeGranted($ticket);

        if ($command->assignee !== null) {
            $assignee = $this->userService->getByUser(new User($command->assignee, User::TYPE_ORO));
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found');
            }
            $ticket->assign($assignee);
        } else {
            $ticket->unAssign();
        }

        $this->ticketRepository->store($ticket);

        $this->dispatchWorkflowEvent(
            $this->doctrineRegistry,
            $this->dispatcher,
            $ticket
        );
    }

    /**
     * Delete Ticket by id
     * @param $id
     * @return null
     *
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($id)
    {
        $ticket = $this->loadTicketById($id);
        $this->isGranted('DELETE', $ticket);
        $this->processDeleteTicket($ticket);
    }

    /**
     * Delete Ticket by key
     * @param string $key
     * @return void
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function deleteTicketByKey($key)
    {
        $ticket = $this->loadTicketByTicketKey(TicketKey::from($key));
        $this->isGranted('DELETE', $ticket);
        $this->processDeleteTicket($ticket);
    }

    /**
     * @param Ticket $ticket
     * @return void
     */
    private function processDeleteTicket(Ticket $ticket)
    {
        $attachments = $ticket->getAttachments();

        foreach ($attachments as $attachment) {
            $this->attachmentManager->deleteAttachment($attachment);
        }
        $this->ticketRepository->remove($ticket);

        $this->dispatchWorkflowEvent(
            $this->doctrineRegistry,
            $this->dispatcher,
            $ticket
        );
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param string $operation
     * @param string|Ticket $entity
     * @throws ForbiddenException
     */
    private function isGranted($operation, $entity)
    {
        if (!$this->authorizationService->isActionPermitted($operation, $entity)) {
            throw new ForbiddenException('Not enough permissions.');
        }
    }

    /**
     * Verify that current user assignee is current user
     *
     * @param Ticket $entity
     * @throws ForbiddenException
     */
    private function isAssigneeGranted(Ticket $entity)
    {
        $user = $this->authorizationService->getLoggedUser();
        if (is_null($entity->getAssignee()) || $entity->getAssignee()->getId() != $user->getId()) {
            $this->isGranted('EDIT', $entity);
        }
    }

    /**
     * Update certain properties of the Ticket
     * @param Command\UpdatePropertiesCommand $command
     * @return Ticket
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command)
    {
        $ticket = $this->loadTicketById($command->id);

        $this->isGranted('EDIT', $ticket);

        $ticket->updateProperties($command->properties);
        $this->ticketRepository->store($ticket);

        $this->loadTagging($ticket);

        $this->dispatchWorkflowEvent(
            $this->doctrineRegistry,
            $this->dispatcher,
            $ticket
        );

        return $ticket;
    }

    /**
     * Update certain properties of the Ticket by key
     * @param Command\UpdatePropertiesCommand $command
     * @return Ticket
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function updatePropertiesByKey(Command\UpdatePropertiesCommand $command)
    {
        $ticket = $this->loadTicketByKey($command->key);
        $command->id = $ticket->getId();

        return $this->updateProperties($command);
    }


    /**
     * @return TicketRepository
     */
    protected function getTicketRepository()
    {
        return $this->ticketRepository;
    }

    /**
     * @return AuthorizationService
     */
    protected function getAuthorizationService()
    {
        return $this->authorizationService;
    }

    /**
     * @param Ticket $ticket
     */
    private function removePrivateComments(Ticket $ticket)
    {
        $user = $this->authorizationService->getLoggedUser();

        if (!$user instanceof ApiUser) {
            return;
        }

        $comments = $ticket->getComments();
        foreach ($comments as $comment) {
            if (!$comment->isPrivate()) {
                $comments->removeElement($comment);
            }
        }
    }

    /**
     * @param Ticket $ticket
     */
    private function loadTagging(Ticket $ticket)
    {
        if ($this->loggedUser instanceof OroUser) {
            $this->tagManager->loadTagging($ticket);
        }
    }
}
