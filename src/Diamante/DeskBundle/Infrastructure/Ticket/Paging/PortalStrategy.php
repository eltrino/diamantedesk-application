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

namespace Diamante\DeskBundle\Infrastructure\Ticket\Paging;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\TagBundle\Entity\TagManager;

/**
 * Class PortalStrategy
 * @package Diamante\DeskBundle\Infrastructure\Ticket\Paging
 */
class PortalStrategy extends AbstractStrategy implements Strategy
{
    /**
     * @return mixed
     */
    public function getFilterCallback()
    {
        return [$this, 'filter'];
    }

    /**
     * @return mixed
     */
    public function getCountCallback()
    {
        return [$this, 'count'];
    }

    /**
     * @param $qb
     * @return mixed
     */
    public function filter(QueryBuilder $qb)
    {
        $user = $this->getUser();
        $qb->andWhere(DoctrineGenericRepository::SELECT_ALIAS . '.reporter = :reporter');

        $watchedTickets = $this->getWatchedTicketsIds($user, $qb);
        if (!empty($watchedTickets)) {
            $qb->orWhere($qb->expr()->in('e.id', array_reverse($watchedTickets)));
        }
        $qb->setParameter('reporter', $user);
        $conditions[] = ['w', 'userType', 'eq', $user];
        return $conditions;
    }

    /**
     * @param User $user
     * @param QueryBuilder $qb
     * @return array|null
     */
    protected function getWatchedTicketsIds(User $user, QueryBuilder $qb)
    {
        $qb = $qb->getEntityManager()->createQueryBuilder();
        $qb->select('IDENTITY(w.ticket)')
            ->from('DiamanteDeskBundle:WatcherList', 'w')
            ->andWhere('w.userType = :user')
            ->setParameter('user', (string)$user);

        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_SCALAR);
            $result = array_map(function($item) {
                return (int)current($item);
            }, $result);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param QueryBuilder $qb
     * @param $entityName
     * @return mixed|void
     */
    public function count(QueryBuilder $qb, $entityName)
    {
        $qb->select($qb->expr()->count(DoctrineGenericRepository::SELECT_ALIAS))
            ->from($entityName, DoctrineGenericRepository::SELECT_ALIAS)
            ->leftJoin(DoctrineGenericRepository::SELECT_ALIAS . '.watcherList', 'w')
            ->orWhere('e.reporter = :user')
            ->orWhere('w.userType = :user')
            ->setParameter('user', (string)$this->user);
    }

    /**
     * @param $tickets
     * @param TagManager $tagManager
     * @return mixed
     */
    public function afterResult($tickets, $tagManager)
    {
        return $tickets;
    }
}