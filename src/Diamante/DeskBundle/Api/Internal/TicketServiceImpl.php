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

use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Api\TicketService;
use Diamante\DeskBundle\Entity\TicketHistory;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketHistoryRepository;
use Diamante\DeskBundle\Model\Attachment\Exception\AttachmentNotFoundException;
use Diamante\DeskBundle\Model\Attachment\Manager as AttachmentManager;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketMovedException;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketNotFoundException;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketBuilder;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;

class TicketServiceImpl implements TicketService
{
    use Shared\AttachmentTrait;

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
     * @var EntityRepository
     */
    private $oroUserRepository;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var DoctrineTicketHistoryRepository
     */
    private $ticketHistoryRepository;

    /**
     * @var OroUser|ApiUser
     */
    protected $loggedUser;

    /**
     * @param Registry                 $doctrineRegistry
     * @param TicketBuilder            $ticketBuilder
     * @param AttachmentManager        $attachmentManager
     * @param AuthorizationService     $authorizationService
     * @param EventDispatcherInterface $dispatcher
     * @param TokenStorageInterface    $tokenStorage
     */
    public function __construct(
        Registry $doctrineRegistry,
        TicketBuilder $ticketBuilder,
        AttachmentManager $attachmentManager,
        AuthorizationService $authorizationService,
        EventDispatcherInterface $dispatcher,
        TokenStorageInterface $tokenStorage
    ) {
        $this->doctrineRegistry        = $doctrineRegistry;
        $this->ticketBuilder           = $ticketBuilder;
        $this->attachmentManager       = $attachmentManager;
        $this->authorizationService    = $authorizationService;
        $this->dispatcher              = $dispatcher;
        $this->ticketRepository        = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:Ticket');
        $this->branchRepository        = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:Branch');
        $this->ticketHistoryRepository = $this->doctrineRegistry->getRepository('DiamanteDeskBundle:TicketHistory');
        $this->oroUserRepository       = $this->doctrineRegistry->getRepository('OroUserBundle:User');
        $this->loggedUser              = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
    }

    /**
     * Load Ticket by given ticket id
     *
     * @param int $id
     *
     * @return Ticket
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function loadTicket($id)
    {
        $ticket = $this->loadTicketById($id);

        $this->isGranted('VIEW', $ticket);

        return $ticket;
    }

    /**
     * Load Ticket by given Ticket Key
     *
     * @param string $key
     *
     * @return Ticket
     *
     * @throws ForbiddenException
     * @throws TicketNotFoundException
     */
    public function loadTicketByKey($key)
    {
        $ticketHistory = $this->ticketHistoryRepository->findOneByTicketKey($key);
        if ($ticketHistory) {
            $ticket     = $this->ticketRepository->get($ticketHistory->getTicket()->getId());
            $currentKey = (string)$ticket->getKey();
            throw new TicketMovedException($currentKey);
        } else {
            $ticketKey = TicketKey::from($key);
            $ticket    = $this->loadTicketByTicketKey($ticketKey);
        }

        $this->isGranted('VIEW', $ticket);

        return $ticket;
    }

    /**
     * @param TicketKey $ticketKey
     *
     * @return Ticket
     *
     * @throws TicketNotFoundException
     */
    private function loadTicketByTicketKey(TicketKey $ticketKey)
    {
        if ($this->loggedUser instanceof ApiUser) {
            $ticket = $this->ticketRepository->getByTicketKeyWithoutPrivateComments($ticketKey);
        } else {
            $ticket = $this->ticketRepository->getByTicketKey($ticketKey);
        }

        if (is_null($ticket)) {
            throw new TicketNotFoundException('Ticket loading failed, ticket not found.');
        }

        return $ticket;
    }

    /**
     * @param int $id
     *
     * @return Ticket
     * @throws TicketNotFoundException if Ticket does not exists
     */
    private function loadTicketById($id)
    {
        /** @var Ticket $ticket */
        if ($this->loggedUser instanceof ApiUser) {
            $ticket = $this->ticketRepository->getByTicketIdWithoutPrivateComments($id);
        } else {
            $ticket = $this->ticketRepository->get($id);
        }

        if (is_null($ticket)) {
            throw new TicketNotFoundException('Ticket loading failed, ticket not found.');
        }

        return $ticket;
    }

    /**
     * List Ticket attachments
     *
     * @param int $id
     *
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
     *
     * @param Command\RetrieveTicketAttachmentCommand $command
     *
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
     *
     * @param Command\AddTicketAttachmentCommand $command
     *
     * @return array
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addAttachmentsForTicket(Command\AddTicketAttachmentCommand $command)
    {
        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf(AttachmentInput::class);

        $ticket = $this->loadTicketById($command->ticketId);

        $this->isGranted('EDIT', $ticket);

        $attachments = $this->createAttachments($command, $ticket);

        $this->ticketRepository->store($ticket);

        return $attachments;
    }

    /**
     * Remove Attachment from Ticket
     *
     * @param Command\RemoveTicketAttachmentCommand $command
     * @param boolean                               $flush
     *
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
     *
     * @param Command\CreateTicketCommand $command
     *
     * @return Ticket
     * @throws \Exception
     */
    public function createTicket(Command\CreateTicketCommand $command)
    {
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Ticket');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf('Diamante\DeskBundle\Api\Dto\AttachmentInput');

        $em = $this->doctrineRegistry->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $this->ticketBuilder->setSubject($command->subject);
            $this->ticketBuilder->setDescription($command->description);
            $this->ticketBuilder->setBranchId($command->branch);
            $this->ticketBuilder->setReporter($command->reporter);
            $this->ticketBuilder->setAssignee($command->assignee);
            $this->ticketBuilder->setPriority($command->priority);
            $this->ticketBuilder->setSource($command->source);
            $this->ticketBuilder->setStatus($command->status);

            $ticket = $this->ticketBuilder->build();
            $em->lock($ticket->getBranch(), LockMode::PESSIMISTIC_READ);

            $this->createAttachments($command, $ticket);

            $em->persist($ticket);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }

        return $ticket;
    }

    /**
     * Update Ticket
     *
     * @param Command\UpdateTicketCommand $command
     *
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
            $assignee          = $ticket->getAssignee();
            $currentAssigneeId = empty($assignee) ? null : $assignee->getId();

            if ($command->assignee !== $currentAssigneeId) {
                $assignee = $this->oroUserRepository->find($command->assignee);
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

        $this->createAttachments($command, $ticket);

        $this->doctrineRegistry->getManager()->persist($ticket);
        $this->doctrineRegistry->getManager()->flush();

        return $ticket;
    }

    /**
     * @param Command\UpdateStatusCommand $command
     *
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

        return $ticket;
    }

    /**
     * @@param Command\MoveTicketCommand $command
     * @return void
     * @throws \RuntimeException if unable to load required ticket
     */
    public function moveTicket(Command\MoveTicketCommand $command)
    {
        $em = $this->doctrineRegistry->getManager();
        $em->getConnection()->beginTransaction();

        try {
            $ticket = $this->loadTicketById($command->id);
            $em->lock($command->branch, LockMode::PESSIMISTIC_READ);

            $this->ticketHistoryRepository->store(new TicketHistory($ticket));
            $ticket->move($command->branch);
            $em->persist($ticket);
            $em->flush();
            $em->getConnection()->commit();
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw new TicketMovedException($e->getMessage());
        }
    }

    /**
     * Assign Ticket to specified User
     *
     * @param Command\AssigneeTicketCommand $command
     *
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket(Command\AssigneeTicketCommand $command)
    {
        $ticket = $this->loadTicketById($command->id);

        $this->isAssigneeGranted($ticket);

        if ($command->assignee !== null) {
            $assignee = $this->oroUserRepository->find($command->assignee);
            if (is_null($assignee)) {
                throw new \RuntimeException('Assignee loading failed, assignee not found');
            }
            $ticket->assign($assignee);
        } else {
            $ticket->unAssign();
        }

        $this->ticketRepository->store($ticket);
    }

    /**
     * Delete Ticket by id
     *
     * @param $id
     *
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
     *
     * @param string $key
     *
     * @return void
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function deleteTicketByKey($key)
    {
        $ticketHistory = $this->ticketHistoryRepository->findOneByTicketKey($key);

        if ($ticketHistory) {
            $ticket = $this->ticketRepository->get($ticketHistory->getTicket()->getId());
        } else {
            $ticketKey = TicketKey::from($key);
            $ticket    = $this->loadTicketByTicketKey($ticketKey);
        }

        $this->isGranted('DELETE', $ticket);
        $this->processDeleteTicket($ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return void
     */
    private function processDeleteTicket(Ticket $ticket)
    {
        $attachments = $ticket->getAttachments();

        foreach ($attachments as $attachment) {
            $this->attachmentManager->deleteAttachment($attachment);
        }

        $this->ticketRepository->remove($ticket);
    }

    /**
     * Verify permissions through Oro Platform security bundle
     *
     * @param string        $operation
     * @param string|Ticket $entity
     *
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
     *
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
     *
     * @param Command\UpdatePropertiesCommand $command
     *
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

        return $ticket;
    }

    /**
     * Update certain properties of the Ticket by key
     *
     * @param Command\UpdatePropertiesCommand $command
     *
     * @return Ticket
     *
     * @throws TicketNotFoundException
     * @throws ForbiddenException
     */
    public function updatePropertiesByKey(Command\UpdatePropertiesCommand $command)
    {
        $ticket      = $this->loadTicketByKey($command->key);
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
}
