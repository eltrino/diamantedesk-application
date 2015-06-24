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
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notifier;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Api\Command\AssigneeTicketCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;
use Diamante\DeskBundle\Api\Command\UpdateTicketCommand;
use Diamante\DeskBundle\Api\Command\MoveTicketCommand;
use Diamante\DeskBundle\Model\Ticket\TicketBuilder;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Entity\TicketHistory;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketMovedException;

class TicketServiceImpl implements TicketService
{
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
     * @var NotificationDeliveryManager
     */
    private $notificationDeliveryManager;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var DoctrineGenericRepository
     */
    private $ticketHistoryRepository;

    public function __construct(TicketRepository $ticketRepository,
                                Repository $branchRepository,
                                TicketBuilder $ticketBuilder,
                                AttachmentManager $attachmentManager,
                                UserService $userService,
                                AuthorizationService $authorizationService,
                                EventDispatcher $dispatcher,
                                NotificationDeliveryManager $notificationDeliveryManager,
                                Notifier $notifier,
                                DoctrineGenericRepository $ticketHistoryRepository
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->branchRepository = $branchRepository;
        $this->ticketBuilder = $ticketBuilder;
        $this->userService = $userService;
        $this->attachmentManager = $attachmentManager;
        $this->authorizationService = $authorizationService;
        $this->dispatcher = $dispatcher;
        $this->notificationDeliveryManager = $notificationDeliveryManager;
        $this->notifier = $notifier;
        $this->ticketHistoryRepository = $ticketHistoryRepository;
    }

    /**
     * Load Ticket by given ticket id
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function loadTicket($id)
    {
        $ticket = $this->loadTicketById($id);
        $this->isGranted('VIEW', $ticket);
        return $ticket;
    }

    /**
     * Load Ticket by given Ticket Key
     * @param string $key
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
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

        $this->isGranted('VIEW', $ticket);

        return $ticket;
    }

    /**
     * @param TicketKey $ticketKey
     * @return Ticket
     */
    private function loadTicketByTicketKey(TicketKey $ticketKey)
    {
        $ticket = $this->ticketRepository
            ->getByTicketKey($ticketKey);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $this->removePrivateComments($ticket);

        return $ticket;
    }

    /**
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws TicketNotFoundException if Ticket does not exists
     */
    private function loadTicketById($id)
    {
        /** @var \Diamante\DeskBundle\Model\Ticket\Ticket $ticket */
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
     * @return array|Attachment[]
     */
    public function listTicketAttachments($id)
    {
        $ticket = $this->loadTicket($id);
        $this->isGranted('VIEW', $ticket);
        return $ticket->getAttachments();
    }

    /**
     * Retrieves Ticket Attachment
     * @param RetrieveTicketAttachmentCommand $command
     * @return \Diamante\DeskBundle\Entity\Attachment
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function getTicketAttachment(RetrieveTicketAttachmentCommand $command)
    {
        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('VIEW', $ticket);

        $attachment = $ticket->getAttachment($command->attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Ticket has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Adds Attachments for Ticket
     * @param AddTicketAttachmentCommand $command
     * @return array
     */
    public function addAttachmentsForTicket(AddTicketAttachmentCommand $command)
    {
        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        $attachments = [];

        if (is_array($command->attachmentsInput) && false === empty($command->attachmentsInput)) {
            foreach ($command->attachmentsInput as $each) {
                $attachments[] = $this->attachmentManager->createNewAttachment($each->getFilename(), $each->getContent(), $ticket);
            }
        }

        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);

        return $attachments;
    }

    /**
     * Remove Attachment from Ticket
     * @param RemoveTicketAttachmentCommand $command
     * @return string $ticketKey
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function removeAttachmentFromTicket(RemoveTicketAttachmentCommand $command)
    {
        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        $attachment = $ticket->getAttachment($command->attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Ticket has no such attachment.');
        }

        $ticket->removeAttachment($attachment);
        $this->ticketRepository->store($ticket);

        $this->attachmentManager->deleteAttachment($attachment);

        $this->dispatchEvents($ticket);
        return $ticket->getKey();
    }

    /**
     * Create Ticket
     * @param CreateTicketCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket(CreateTicketCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Ticket');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $this->ticketBuilder
            ->setSubject($command->subject)
            ->setDescription($command->description)
            ->setBranchId($command->branch)
            ->setReporter($command->reporter)
            ->setAssigneeId($command->assignee)
            ->setPriority($command->priority)
            ->setSource($command->source)
            ->setStatus($command->status);

        $ticket = $this->ticketBuilder->build();

        if (is_array($command->attachmentsInput) && false === empty($command->attachmentsInput)) {
            foreach ($command->attachmentsInput as $each) {
                $this->attachmentManager->createNewAttachment($each->getFilename(), $each->getContent(), $ticket);
            }
        }

        $this->ticketRepository->store($ticket);
        $this->dispatchEvents($ticket);

        return $ticket;
    }

    /**
     * Update Ticket
     *
     * @param UpdateTicketCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket(UpdateTicketCommand $command)
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
            $assignee
        );

        if (is_array($command->attachmentsInput) && false === empty($command->attachmentsInput)) {
            foreach ($command->attachmentsInput as $each) {
                $this->attachmentManager->createNewAttachment($each->getFilename(), $each->getContent(), $ticket);
            }
        }

        $this->ticketRepository->store($ticket);
        $this->dispatchEvents($ticket);

        return $ticket;
    }

    /**
     * @@param UpdateStatusCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function updateStatus(UpdateStatusCommand $command)
    {
        $ticket = $this->loadTicketById($command->ticketId);

        $this->isAssigneeGranted($ticket);

        $ticket->updateStatus(new Status($command->status));
        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);

        return $ticket;
    }

    /**
     * @@param MoveTicketCommand $command
     * @return void
     * @throws \RuntimeException if unable to load required ticket
     */
    public function moveTicket(MoveTicketCommand $command)
    {
        $ticket = $this->loadTicketById($command->id);
        $this->ticketHistoryRepository->store(new TicketHistory($ticket->getId(), $ticket->getKey()));
        $ticket->move($command->branch);
        $this->ticketRepository->store($ticket);

        //Remove old key from history to prevent loop redirects
        if ($oldHistory = $this->ticketHistoryRepository->findOneByTicketKey($ticket->getKey())) {
            $this->ticketHistoryRepository->remove($oldHistory);
        }

        $this->dispatchEvents($ticket);
    }

    /**
     * Assign Ticket to specified User
     * @param AssigneeTicketCommand $command
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket(AssigneeTicketCommand $command)
    {
        $ticket = $this->loadTicketById($command->id);

        $this->isAssigneeGranted($ticket);

        if ($command->assignee) {
            $assignee = $this->userService->getByUser(new User($command->assignee, User::TYPE_ORO));
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found.');
            }
            $ticket->assign($assignee);
        } else {
            $ticket->unAssign();
        }

        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);
    }

    /**
     * Delete Ticket by id
     * @param $id
     * @return null
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
        $ticket->delete();
        foreach ($attachments as $attachment) {
            $this->attachmentManager->deleteAttachment($attachment);
        }
        $this->dispatchEvents($ticket);
        $this->ticketRepository->remove($ticket);
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
        if (!$this->authorizationService->isActionPermitted($operation, $entity)) {
            throw new ForbiddenException("Not enough permissions.");
        }
    }

    /**
     * Verify that current user assignee is current user
     *
     * @param Ticket $entity
     * @throws \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     */
    private function isAssigneeGranted(Ticket $entity)
    {
        $user = $this->authorizationService->getLoggedUser();
        if (is_null($entity->getAssignee()) || $entity->getAssignee()->getId() != $user->getId()) {
            $this->isGranted('EDIT', $entity);
        }
    }

    /**
     * Dispatches events
     *
     * @param Ticket $ticket
     */
    private function dispatchEvents(Ticket $ticket)
    {
        $events = $ticket->getRecordedEvents();

        if (empty($events)) {
            return;
        }

        foreach ($events as $event) {
            $this->dispatcher->dispatch($event->getEventName(), $event);
        }

        $this->notificationDeliveryManager->deliver($this->notifier);
    }

    /**
     * Update certain properties of the Ticket
     * @param Command\UpdatePropertiesCommand $command
     * @return Ticket
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command)
    {
        /**
         * @var $ticket \Diamante\DeskBundle\Model\Ticket\Ticket
         */
        $ticket = $this->loadTicketById($command->id);

        $this->isGranted('EDIT', $ticket);

        $ticket->updateProperties($command->properties);
        $this->ticketRepository->store($ticket);
        $this->dispatchEvents($ticket);

        return $ticket;
    }

    /**
     * Update certain properties of the Ticket by key
     * @param Command\UpdatePropertiesCommand $command
     * @return Ticket
     */
    public function updatePropertiesByKey(Command\UpdatePropertiesCommand $command)
    {
        /**
         * @var $ticket \Diamante\DeskBundle\Model\Ticket\Ticket
         */
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
        $commentsList = $comments->toArray();
        $comments->clear();
        foreach($commentsList as $comment) {
            if(!$comment->isPrivate()) {
                $comments->add($comment);
            }
        }
        $comments->takeSnapshot();
    }
}
