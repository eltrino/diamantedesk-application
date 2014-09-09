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
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Form\Command\CreateTicketCommand;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\TicketFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository;
use Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

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

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    public function __construct(TicketRepository $ticketRepository,
                                BranchRepository $branchRepository,
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

        $this->isGranted('VIEW', $ticket);

        $attachment = $ticket->getAttachment($attachmentId);
        if (!$attachment) {
            throw new \RuntimeException('Attachment loading failed. Ticket has no such attachment.');
        }
        return $attachment;
    }

    /**
     * Adds Attachments for Ticket
     * @param array $attachmentsInput array of AttachmentInput DTOs
     * @param $ticketId
     * @return void
     */
    public function addAttachmentsForTicket(array $attachmentsInput, $ticketId)
    {
        $ticket = $this->ticketRepository->get($ticketId);

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $this->isGranted('EDIT', $ticket);

        $this->attachmentService->createAttachmentsForItHolder($attachmentsInput, $ticket);
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

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $this->isGranted('EDIT', $ticket);

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
                                  UserService $userService,
                                  SecurityFacade $securityFacade
    ) {
        return new TicketServiceImpl(
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Ticket'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Branch'),
            new TicketFactory(),
            $attachmentService,
            $userService,
            $securityFacade
        );
    }

    /**
     * Create Ticket
     * @param $branchId
     * @param $subject
     * @param $description
     * @param $reporterId
     * @param $assigneeId
     * @param $priority
     * @param $source
     * @param $status
     * @param array $attachmentInputs
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($branchId, $subject, $description, $reporterId, $assigneeId, $priority, $source, $status = null, array $attachmentInputs = null)
    {
        $this->isGranted('CREATE', 'Entity:EltrinoDiamanteDeskBundle:Ticket');

        \Assert\that($attachmentInputs)->nullOr()->all()
            ->isInstanceOf('Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\AttachmentInput');
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
            ->create($subject,
                $description,
                $branch,
                $reporter,
                $assignee,
                $priority,
                $source,
                $status
            );

        if (is_array($attachmentInputs) && false === empty($attachmentInputs)) {
            $this->attachmentService->createAttachmentsForItHolder($attachmentInputs, $ticket);
        }

        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * Update Ticket
     *
     * @param $ticketId
     * @param $subject
     * @param $description
     * @param $reporterId
     * @param $assigneeId
     * @param $priority
     * @param $status
     * @param $source
     * @param array $attachmentInputs
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket($ticketId, $subject, $description, $reporterId, $assigneeId, $priority, $source, $status, array $attachmentInputs = null)
    {
        \Assert\that($attachmentInputs)->nullOr()->all()
            ->isInstanceOf('Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\AttachmentInput');
        $ticket = $this->ticketRepository->get($ticketId);

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $this->isGranted('EDIT', $ticket);

        $reporter = $ticket->getReporter();
        if ($reporterId != $ticket->getReporterId()) {
            $reporter = $this->userService->getUserById($reporterId);
            if (is_null($reporter)) {
                throw new \RuntimeException('Reporter loading failed, reporter not found.');
            }
        }

        $ticket->update(
            $subject,
            $description,
            $reporter,
            $priority,
            $status,
            $source
        );

        if ($assigneeId != $ticket->getAssigneeId()) {
            $assignee = $this->userService->getUserById($assigneeId);
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found.');
            }
            $ticket->assign($assignee);
        }

        if (is_array($attachmentInputs) && false === empty($attachmentInputs)) {
            $this->attachmentService->createAttachmentsForItHolder($attachmentInputs, $ticket);
        }

        $this->ticketRepository->store($ticket);

        return $ticket;
    }

    /**
     * @param $ticketId
     * @param $status
     * @return \Eltrino\DiamanteDeskBundle\Ticket\Model\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function updateStatus($ticketId, $status)
    {
        $ticket = $this->ticketRepository->get($ticketId);

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $this->isAssigneeGranted($ticket);

        $ticket->updateStatus($status);
        $this->ticketRepository->store($ticket);

        return $ticket;
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

        $this->isAssigneeGranted($ticket);

        $assignee = $this->userService->getUserById($assigneeId);
        if (is_null($assignee)) {
            throw new \RuntimeException('Assignee loading failed, assignee not found.');
        }

        $ticket->assign($assignee);
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
        if ($entity->getAssigneeId() != $this->securityFacade->getLoggedUserId()) {
            $this->isGranted('EDIT', $entity);
        }
    }
}
