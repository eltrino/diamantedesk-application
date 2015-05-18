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
namespace Diamante\DeskBundle\Infrastructure\Persistence;

use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\WatcherListRepository;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Query;

class DoctrineWatcherListRepository extends DoctrineGenericRepository implements WatcherListRepository
{

    public function findByTicket(Ticket $ticket)
    {
        return $this->findBy(array('ticket' => $ticket->getId()));
    }

    public function findByUser(User $user)
    {
        return $this->findBy(array('userType' => (string)$user));
    }

}
