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

    /**
     * @param Ticket $ticket
     * @return array
     */
    public function findByTicket(Ticket $ticket)
    {
        return $this->findBy(['ticket' => $ticket->getId()]);
    }

    /**
     * @param User $user
     * @return array
     */
    public function findByUser(User $user)
    {
        return $this->findBy(['userType' => (string)$user]);
    }

    /**
     * @param Ticket $ticket
     * @param User $user
     * @return null|object
     */
    public function findOne(Ticket $ticket, User $user)
    {
        return $this->findOneBy([
            'userType' => (string)$user,
            'ticket' => $ticket->getId()
        ]);
    }

    /**
     * @param User $user
     * @return integer
     */
    public function removeByUser(User $user)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete('DiamanteDeskBundle:WatcherList', 'w')
            ->where($qb->expr()->eq('w.userType', ':userType'))
            ->setParameter(':userType', $user);

        return $qb->getQuery()->execute();
    }
}
