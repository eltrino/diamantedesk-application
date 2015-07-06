<?php

namespace Diamante\DeskBundle\Model\Ticket;

use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\UserBundle\Model\User;

/**
 * Interface WatcherListRepository
 * @package Diamante\DeskBundle\Entity
 */
interface WatcherListRepository extends Repository
{
    /**
     * Find users that watching the ticket
     *
     * @param Ticket $ticket
     * @return array
     */
    public function findByTicket(Ticket $ticket);

    /**
     * Find what are tickets the user is watching
     *
     * @param User $user
     * @return array
     */
    public function findByUser(User $user);

    /**
     * @param Ticket $ticket
     * @param User $user
     * @return null|object
     */
    public function findOne(Ticket $ticket, User $user);

    /**
     * @param User $user
     * @return integer
     */
    public function removeByUser(User $user);
}
