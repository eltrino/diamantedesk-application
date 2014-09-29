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

namespace Eltrino\DiamanteDeskBundle\Api\Internal;

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Api\TicketService;
use Eltrino\DiamanteDeskBundle\Api\Command;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket;
use Eltrino\DiamanteDeskBundle\Model\Shared\Repository;
use Eltrino\DiamanteDeskBundle\Model\Ticket\AttachmentService;
use Eltrino\DiamanteDeskBundle\Model\Ticket\TicketFactory;
use Eltrino\DiamanteDeskBundle\Model\Shared\UserService;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Eltrino\DiamanteDeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\AddTicketAttachmentCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\RemoveTicketAttachmentCommand;

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
     * @var AttachmentService
     */
    private $attachmentService;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var Internal\UserService
     */
    private $userService;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    public function __construct(Repository $ticketRepository,
                                Repository $branchRepository,
                                TicketFactory $ticketFactory,
                                AttachmentService $attachmentService,
                                UserService $userService,
                                SecurityFacade $securityFacade
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->branchRepository = $branchRepository;
        $this->ticketFactory = $ticketFactory;
        $this->userService = $userService;
        $this->attachmentService = $attachmentService;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Load Ticket by given ticket id
     * @param int $ticketId
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     */
    public function loadTicket($ticketId)
    {
        return $this->loadTicketBy($ticketId);
    }

    /**
     * @param int $ticketId
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     */
    private function loadTicketBy($ticketId)
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
     * @return \Eltrino\DiamanteDeskBundle\Entity\Attachment
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
        $ticket = $this->loadTicketBy($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        $this->attachmentService->createAttachmentsForItHolder($command->attachments, $ticket);
        $this->ticketRepository->store($ticket);
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
        $this->attachmentService->removeAttachmentFromItHolder($attachment);
        $ticket->removeAttachment($attachment);
        $this->ticketRepository->store($ticket);
    }

    /**
     * Create Ticket
     * @param Command\CreateTicketCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket(Command\CreateTicketCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:EltrinoDiamanteDeskBundle:Ticket');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Eltrino\DiamanteDeskBundle\Api\Dto\AttachmentInput');
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
            $this->attachmentService->createAttachmentsForItHolder($command->attachmentsInput, $ticket);
        }

        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * Update Ticket
     *
     * @param UpdateTicketCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket(Command\UpdateTicketCommand $command)
    {
        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Eltrino\DiamanteDeskBundle\Api\Dto\AttachmentInput');

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
            $this->attachmentService->createAttachmentsForItHolder($command->attachmentsInput, $ticket);
        }

        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * @@param Command\UpdateStatusCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function updateStatus(Command\UpdateStatusCommand $command)
    {
        $ticket = $this->loadTicketBy($command->ticketId);

        $this->isAssigneeGranted($ticket);

        $ticket->updateStatus($command->status);
        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * Assign Ticket to specified User
     * @param Command\AssigneeTicketCommand $command
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket(Command\AssigneeTicketCommand $command)
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
    }

    /**
     * Delete Ticket
     * @param $ticketId
     * @return null
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($ticketId)
    {
        $ticket = $this->loadTicketBy($ticketId);

        $this->isGranted('DELETE', $ticket);

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
}
