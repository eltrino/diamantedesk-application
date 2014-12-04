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
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\EventListener\Mail\TicketProcessManager;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Api\Command\AssigneeTicketCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;
use Diamante\DeskBundle\Api\Command\UpdateTicketCommand;
use Diamante\DeskBundle\Model\Ticket\TicketBuilder;
use Diamante\DeskBundle\Model\Shared\UserService;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var TicketProcessManager
     */
    private $processManager;

    public function __construct(TicketRepository $ticketRepository,
                                Repository $branchRepository,
                                TicketBuilder $ticketBuilder,
                                AttachmentManager $attachmentManager,
                                UserService $userService,
                                SecurityFacade $securityFacade,
                                EventDispatcher $dispatcher,
                                TicketProcessManager $processManager
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->branchRepository = $branchRepository;
        $this->ticketBuilder = $ticketBuilder;
        $this->userService = $userService;
        $this->attachmentManager = $attachmentManager;
        $this->securityFacade = $securityFacade;
        $this->dispatcher = $dispatcher;
        $this->processManager = $processManager;
    }

    /**
     * Load Ticket by given ticket id
     * @param int $ticketId
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function loadTicket($ticketId)
    {
        $ticket = $this->loadTicketById($ticketId);
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
        $ticketKey = TicketKey::from($key);
        $ticket = $this->loadTicketByTicketKey($ticketKey);
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
        return $ticket;
    }

    /**
     * @param int $ticketId
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    private function loadTicketById($ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }
        return $ticket;
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
     * @return void
     */
    public function addAttachmentsForTicket(AddTicketAttachmentCommand $command)
    {
        \Assert\that($command->attachments)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        if (is_array($command->attachments) && false === empty($command->attachments)) {
            foreach ($command->attachments as $each) {
                $this->attachmentManager->createNewAttachment($each->getFilename(), $each->getContent(), $ticket);
            }
        }

        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);
    }

    /**
     * Remove Attachment from Ticket
     * @param RemoveTicketAttachmentCommand $command
     * @return void
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
        $this->attachmentManager->deleteAttachment($attachment);
        $ticket->removeAttachment($attachment);
        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);
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
            ->setReporterId($command->reporter)
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
        if ($command->reporter != $ticket->getReporterId()) {
            $reporter = $this->userService->getUserById($command->reporter);
            if (is_null($reporter)) {
                throw new \RuntimeException('Reporter loading failed, reporter not found.');
            }
        }

        $ticket->update(
            $command->subject,
            $command->description,
            $reporter,
            $command->priority,
            $command->status,
            $command->source
        );

        if ($command->assignee) {
            $assignee = $this->userService->getUserById($command->assignee);
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found.');
            }
            $ticket->assign($assignee);
        } else {
            $ticket->unassign();
        }

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

        $ticket->updateStatus($command->status);
        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);

        return $ticket;
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
            $assignee = $this->userService->getUserById($command->assignee);
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found.');
            }
            $ticket->assign($assignee);
        } else {
            $ticket->unassign();
        }

        $this->ticketRepository->store($ticket);

        $this->dispatchEvents($ticket);
    }

    /**
     * Delete Ticket by id
     * @param int $id
     * @return void
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
        $this->ticketRepository->remove($ticket);
        foreach ($attachments as $attachment) {
            $this->attachmentManager->deleteAttachment($attachment);
        }
        $this->dispatchEvents($ticket);
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

    /**
     * Verify that current user assignee is current user
     *
     * @param Ticket $entity
     * @throws \Oro\Bundle\SecurityBundle\Exception\ForbiddenException
     */
    private function isAssigneeGranted(Ticket $entity)
    {
        if (is_null($entity->getAssignee()) || $entity->getAssignee()->getId() != $this->securityFacade->getLoggedUserId()) {
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
        foreach ($ticket->getRecordedEvents() as $event) {
            $this->dispatcher->dispatch($event->getEventName(), $event);
        }

        if (count($this->processManager->getEventsHistory())) {
            $this->processManager->process();
        }
    }
}
