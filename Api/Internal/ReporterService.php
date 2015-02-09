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

use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\CommentRepository;
use Diamante\DeskBundle\Model\User\User;

class ReporterService {
    /**
     * @var TicketRepository
     */
    private $ticketRepository;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @param TicketRepository $ticketRepository
     * @param CommentRepository $commentRepository
     */
    public function __construct(TicketRepository $ticketRepository, CommentRepository $commentRepository) {
        $this->ticketRepository = $ticketRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @param User $user
     */
    public function cleanupUser(User $user)
    {
        $this->ticketRepository->removeTicketReporter($user);
        $this->commentRepository->removeCommentAuthor($user);
    }
}