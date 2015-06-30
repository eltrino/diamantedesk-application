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

use Diamante\DeskBundle\Api\WatchersService;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketRepository;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineWatcherListRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Entity\WatcherList;
use Diamante\UserBundle\Infrastructure\DiamanteUserFactory;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Diamante\UserBundle\Api\UserService;
use Doctrine\ORM\EntityManager;

class WatchersServiceImpl implements WatchersService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var DoctrineWatcherListRepository
     */
    protected $watcherListRepository;

    /**
     * @var DoctrineDiamanteUserRepository
     */
    protected $diamanteUserRepository;

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var DoctrineTicketRepository
     */
    protected $ticketRepository;

    /**
     * @var DiamanteUserFactory
     */
    protected $diamanteUserFactory;

    /**
     * @var UserService
     */
    protected $userService;

    public function __construct(
        EntityManager $em,
        DoctrineWatcherListRepository $watcherListRepository,
        DoctrineDiamanteUserRepository $diamanteUserRepository,
        UserManager $userManager,
        DoctrineTicketRepository $ticketRepository,
        DiamanteUserFactory $diamanteUserFactory,
        UserService $userService
    ) {
        $this->em                     = $em;
        $this->watcherListRepository  = $watcherListRepository;
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->userManager            = $userManager;
        $this->ticketRepository       = $ticketRepository;
        $this->diamanteUserFactory    = $diamanteUserFactory;
        $this->userService            = $userService;
    }

    /**
     * @param Ticket $ticket
     * @param User $user
     */
    public function addWatcher(Ticket $ticket, User $user)
    {
        $watcher = $this->watcherListRepository->findOne($ticket, $user);

        if (!$watcher) {
            $ticket = $this->em->merge($ticket);
            $watcher = new WatcherList($ticket, $user);
            $this->watcherListRepository->store($watcher);
        }
    }

    /**
     * @param Ticket $ticket
     * @param User $user
     */
    public function removeWatcher(Ticket $ticket, User $user)
    {
        $watcher = $this->watcherListRepository->findOne($ticket, $user);

        if ($watcher) {
            $this->watcherListRepository->remove($watcher);
        }
    }

    /**
     * Return watchers list
     *
     * @param Ticket $ticket
     *
     * @return array
     */
    public function getWatchers(Ticket $ticket)
    {
        return $ticket->getWatcherList()->getValues();
    }
}