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

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Form\Command\CreateTicketCommand;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\TicketFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService;
use Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository;
use Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository;

class TicketServiceImpl implements TicketService
{
    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var BranchRepository
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

    public function __construct(TicketRepository $ticketRepository,
                                BranchRepository $branchRepository,
                                TicketFactory $ticketFactory,
                                AttachmentService $attachmentService,
                                UserService $userService
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->branchRepository = $branchRepository;
        $this->ticketFactory = $ticketFactory;
        $this->userService = $userService;
        $this->attachmentService = $attachmentService;
    }

    /**
     * Retrieves Ticket Attachment
     * @param $ticketId
     * @param $attachmentId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Attachment
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function getTicketAttachment($ticketId, $attachmentId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }
        $attachment = $ticket->getAttachment($attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Ticket has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Adds Attachments for Ticket
     * @param FilesListDto $filesListDto
     * @param $ticketId
     * @return void
     */
    public function addAttachmentsForTicket(FilesListDto $filesListDto, $ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (!$ticket) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }
        $this->attachmentService->createAttachmentsForItHolder($filesListDto, $ticket);
        $this->ticketRepository->store($ticket);
    }

    /**
     * Remove Attachment from Ticket
     * @param $ticketId
     * @param $attachmentId
     * @return void
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function removeAttachmentFromTicket($ticketId, $attachmentId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (!$ticket) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }
        $attachment = $ticket->getAttachment($attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Ticket has no such attachment.');
        }
        $this->attachmentService->removeAttachmentFromItHolder($attachment);
        $ticket->removeAttachment($attachment);
        $this->ticketRepository->store($ticket);
    }

    public static function create(EntityManager $em,
                                  AttachmentService $attachmentService,
                                  UserService $userService
    ) {
        return new TicketServiceImpl(
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Ticket'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Branch'),
            new TicketFactory(),
            $attachmentService,
            $userService
        );
    }

    /**
     * Create Ticket
     * @param $branchId
     * @param $subject
     * @param $description
     * @param $status
     * @param $reporterId
     * @param $assigneeId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($branchId, $subject, $description, $status, $reporterId, $assigneeId)
    {
        $branch = $this->branchRepository->get($branchId);
        if (is_null($branch)) {
            throw new \RuntimeException('Branch loading failed, branch not found.');
        }

        $reporter = $this->userService->getUserById($reporterId);
        if (is_null($reporter)) {
            throw new \RuntimeException('Reporter loading failed, reporter not found.');
        }

        $assignee = $this->userService->getUserById($assigneeId);
        if (is_null($assignee)) {
            throw new \RuntimeException('Assignee validation failed, assignee not found.');
        }

        $ticket = $this->ticketFactory
            ->create();

        $ticket->create(
            $subject,
            $description,
            $branch,
            $status,
            $reporter,
            $assignee
        );

        $this->ticketRepository->store($ticket);

        return $ticket;

    }

    /**
     * Update Ticket
     * @param $ticketId
     * @param $subject
     * @param $description
     * @param $status
     * @param $assigneeId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket($ticketId, $subject, $description, $status, $assigneeId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $ticket->update(
            $subject,
            $description,
            $status
        );

        if ($assigneeId != $ticket->getAssigneeId()) {
            $assignee = $this->userService->getUserById($assigneeId);
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found.');
            }
            $ticket->assign($assignee);
        }

        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * Delete Ticket
     * @param $ticketId
     * @return null
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $this->ticketRepository->remove($ticket);
    }

    /**
     * Assign Ticket to specified User
     * @param $ticketId
     * @param $assigneeId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket($ticketId, $assigneeId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $assignee = $this->userService->getUserById($assigneeId);
        if (is_null($assignee)) {
            throw new \RuntimeException('Assignee loading failed, assignee not found.');
        }

        $ticket->assign($assignee);
        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * Close Ticket
     * @param $ticketId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function closeTicket($ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $ticket->close();
        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * Reopen Ticket
     * @param $ticketId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function reopenTicket($ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);
        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $ticket->reopen();
        $this->ticketRepository->store($ticket);

        return $ticket;
    }
}
