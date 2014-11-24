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

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\DeskBundle\Api\TicketService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Infrastructure\Shared\Authorization\AuthorizationManager;
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\EventListener\Mail\TicketProcessManager;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Api\Command\AssigneeTicketCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;
use Diamante\DeskBundle\Api\Command\UpdateTicketCommand;
use Diamante\DeskBundle\Model\Ticket\TicketFactory;
use Diamante\DeskBundle\Model\Shared\UserService;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Symfony\Component\EventDispatcher\EventDispatcher;

class TicketServiceImpl implements TicketService
{
    /**
     * @var Repository
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
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var AuthorizationManager
     */
    private $authorizationManager;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var TicketProcessManager
     */
    private $processManager;

    public function __construct(Repository $ticketRepository,
                                Repository $branchRepository,
                                TicketFactory $ticketFactory,
                                AttachmentManager $attachmentManager,
                                UserService $userService,
                                AuthorizationManager $authorizationManager,
                                EventDispatcher $dispatcher,
                                TicketProcessManager $processManager
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->branchRepository = $branchRepository;
        $this->ticketFactory = $ticketFactory;
        $this->userService = $userService;
        $this->attachmentManager = $attachmentManager;
        $this->authorizationManager = $authorizationManager;
        $this->dispatcher = $dispatcher;
        $this->processManager = $processManager;
    }

    /**
     * Load Ticket by given ticket id
     *
     * @ApiDoc(
     *  description="Returns a ticket",
     *  uri="/tickets/{id}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function loadTicket($id)
    {
        $ticket = $this->loadTicketBy($id);
        $this->isGranted('VIEW', $ticket);

        return $ticket;
    }

    /**
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    private function loadTicketBy($id)
    {
        $ticket = $this->ticketRepository->get($id);
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
        $ticket = $this->loadTicketBy($command->ticketId);

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

        $ticket = $this->loadTicketBy($command->ticketId);

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
        $ticket = $this->loadTicketBy($command->ticketId);

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
     *
     * @ApiDoc(
     *  description="Create ticket",
     *  uri="/tickets.{_format}",
     *  method="POST",
     *  resource=true,
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to create ticket"
     *  }
     * )
     *
     * @param CreateTicketCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket(CreateTicketCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Ticket');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');
        $branch = $this->branchRepository->get($command->branch);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed, branch not found.');
        }

        $reporter = $this->userService->getUserById($command->reporter);
        if (is_null($reporter)) {
            throw new \RuntimeException('Reporter loading failed, reporter not found.');
        }

        $assignee = $this->userService->getUserById($command->assignee);
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
                $command->source,
                $command->status
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

        $ticket = $this->loadTicketBy($command->id);

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
        $ticket = $this->loadTicketBy($command->ticketId);

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
        $ticket = $this->loadTicketBy($command->id);

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
     * Delete Ticket
     *
     * @ApiDoc(
     *  description="Delete ticket",
     *  uri="/tickets/{id}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Ticket Id"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete ticket",
     *      404="Returned when the ticket is not found"
     *  }
     * )
     *
     * @param $id
     * @return null
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($id)
    {
        $ticket = $this->loadTicketBy($id);
        $this->isGranted('DELETE', $ticket);
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
        if (!$this->authorizationManager->isActionPermitted($operation, $entity)) {
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

    /**
     * Update certain properties of the Ticket
     *
     * @ApiDoc(
     *  description="Update ticket",
     *  uri="/tickets/{id}.{_format}",
     *  method="PUT",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Branch Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update ticket",
     *      404="Returned when the branch is not found"
     *  }
     * )
     *
     * @param Command\UpdatePropertiesCommand $command
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command)
    {
        /**
         * @var $ticket \Diamante\DeskBundle\Model\Ticket\Ticket
         */
        $ticket = $this->loadTicketBy($command->id);

        $this->isGranted('EDIT', $ticket);

        foreach ($command->properties as $name => $value) {
            $ticket->updateProperty($name, $value);
        }

        $this->ticketRepository->store($ticket);
    }

    /**
     * Retrieves list of all Tickets
     *
     * @ApiDoc(
     *  description="Returns all tickets",
     *  uri="/tickets.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list tickets"
     *  }
     * )
     *
     * @return Ticket[]
     */
    public function listAllTickets()
    {
        $this->isGranted('VIEW', 'Entity:DiamanteDeskBundle:Ticket');

        $tickets = $this->ticketRepository->getAll();
        return $tickets;
    }
}
