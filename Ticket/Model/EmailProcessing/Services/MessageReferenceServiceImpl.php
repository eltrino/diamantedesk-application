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
namespace Eltrino\DiamanteDeskBundle\Ticket\Model\EmailProcessing\Services;

use Doctrine\ORM\EntityManager;

use Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Entity\MessageReference;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\CommentFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\TicketFactory;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService;
use Eltrino\DiamanteDeskBundle\Ticket\Model\EmailProcessing\MessageReferenceRepository;
use Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository;

class MessageReferenceServiceImpl implements MessageReferenceService
{
    /**
     * @var MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var BranchRepository
     */
    private $branchRepository;

    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * @var CommentFactory
     */
    private $commentFactory;

    /**
     * @var UserService
     */
    private $userService;

    public function __construct(
        MessageReferenceRepository $messageReferenceRepository,
        TicketRepository $ticketRepository,
        BranchRepository $branchRepository,
        TicketFactory $ticketFactory,
        CommentFactory $commentFactory,
        UserService $userService
    )
    {
        $this->messageReferenceRepository = $messageReferenceRepository;
        $this->ticketRepository           = $ticketRepository;
        $this->branchRepository           = $branchRepository;
        $this->ticketFactory              = $ticketFactory;
        $this->commentFactory             = $commentFactory;
        $this->userService                = $userService;
    }

    /**
     * Creates Ticket and Message Reference fot it
     *
     * @param $messageId
     * @param $branchId
     * @param $subject
     * @param $description
     * @param $reporterId
     * @param $assigneeId
     * @param null $status
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($messageId, $branchId, $subject, $description, $reporterId, $assigneeId, $status = null)
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
            ->create($subject,
                $description,
                $branch,
                $reporter,
                $assignee,
                $status);

        $this->ticketRepository->store($ticket);
        $this->createMessageReference($messageId, $ticket);

        return $ticket;
    }

    /**
     * Creates Comment for Ticket
     *
     * @param $content
     * @param $authorId
     * @param $messageId
     * @return void
     */
    public function createCommentForTicket($content, $authorId, $messageId)
    {
        $ticket = $this->messageReferenceRepository
            ->getReferenceByMessageId($messageId)
            ->getTicket();

        if (is_null($ticket)) {
            throw new \RuntimeException('Ticket loading failed, ticket not found.');
        }

        $author = $this->userService->getUserById($authorId);
        $comment = $this->commentFactory->create($content, $ticket, $author);

        $ticket->postNewComment($comment);
        $this->ticketRepository->store($ticket);
    }

    /**
     * Create Message Reference
     *
     * @param $messageId
     * @param $ticket
     */
    private function createMessageReference($messageId, $ticket)
    {
        $messageReference = new MessageReference($messageId, $ticket);
        $this->messageReferenceRepository->store($messageReference);
    }

    /**
     * @param EntityManager $em
     * @param UserService $userService
     * @return MessageReferenceServiceImpl
     */
    public static function create(EntityManager $em, UserService $userService) {
        return new MessageReferenceServiceImpl(
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\MessageReference'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Ticket'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Branch'),
            new TicketFactory(),
            new CommentFactory(),
            $userService
        );
    }
} 