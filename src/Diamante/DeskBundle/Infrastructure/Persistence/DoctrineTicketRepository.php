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

use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Shared\Filter\FilterPagingProperties;
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Query;

/**
 * Class DoctrineTicketRepository
 *
 * @package Diamante\DeskBundle\Infrastructure\Persistence
 *
 * @method Ticket findOneByTicketKey(TicketKey $key)
 * @method Ticket[] findByBranch($id)
 * @method Ticket|null get($id)
 */
class DoctrineTicketRepository extends DoctrineGenericRepository implements TicketRepository
{

    /**
     * Find Ticket by given id without private comments
     *
     * @param int $id
     *
     * @return \Diamante\DeskBundle\Entity\Ticket
     */
    public function getByTicketIdWithoutPrivateComments($id)
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select(['t', 'c'])
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->leftJoin('t.comments', 'c', 'WITH', 'c.private = :private')
            ->where('t.id = :ticketId')
            ->setParameters([
                'private'  => false,
                'ticketId' => $id
            ]);

        $ticket = $queryBuilder->getQuery()->getOneOrNullResult();

        if (is_null($ticket)) {
            $ticket = $this->get($id);
        }

        return $ticket;
    }

    /**
     * Find Ticket by given TicketKey
     *
     * @param TicketKey $key
     *
     * @return \Diamante\DeskBundle\Entity\Ticket
     */
    public function getByTicketKey(TicketKey $key)
    {
        $queryBuilder = $this->_em
            ->createQueryBuilder()->select('t')
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->from('DiamanteDeskBundle:Branch', 'b')
            ->where('b.id = t.branch')
            ->andWhere('b.key = :branchKey')
            ->andWhere('t.sequenceNumber = :ticketSequenceNumber')
            ->setParameters(
                [
                    'branchKey'            => $key->getBranchKey(),
                    'ticketSequenceNumber' => $key->getTicketSequenceNumber()
                ]
            );

        $ticket = $queryBuilder->getQuery()->getOneOrNullResult();

        return $ticket;
    }

    /**
     * Find Ticket by given TicketKey without private comments
     *
     * @param TicketKey $key
     *
     * @return \Diamante\DeskBundle\Entity\Ticket
     */
    public function getByTicketKeyWithoutPrivateComments(TicketKey $key)
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        $queryBuilder->select(['t', 'c'])
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->from('DiamanteDeskBundle:Branch', 'b')
            ->leftJoin('t.comments', 'c', 'WITH', 'c.private = :private')
            ->where('b.id = t.branch')
            ->andWhere('b.key = :branchKey')
            ->andWhere('t.sequenceNumber = :ticketSequenceNumber')
            ->setParameters(
                [
                    'private'              => false,
                    'branchKey'            => $key->getBranchKey(),
                    'ticketSequenceNumber' => $key->getTicketSequenceNumber()
                ]
            );

        $ticket = $queryBuilder->getQuery()->getOneOrNullResult();

        return $ticket;
    }

    /**
     * @param UniqueId $uniqueId
     *
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByUniqueId(UniqueId $uniqueId)
    {
        $queryBuilder = $this->_em
            ->createQueryBuilder()->select('t')
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->where('t.uniqueId = :uniqueId')
            ->setParameter('uniqueId', $uniqueId);

        $ticket = $queryBuilder->getQuery()->getOneOrNullResult();

        return $ticket;
    }

    /**
     * Remove reporter id from ticket table
     *
     * @param User $user
     */
    public function removeTicketReporter(User $user)
    {
        $query = $this->_em
            ->createQuery("UPDATE DiamanteDeskBundle:Ticket t SET t.reporter = null WHERE t.reporter = :reporter_id");
        $query->setParameters(
            [
                'reporter_id' => (string)$user,
            ]
        );
        $query->execute();
    }

    /**
     * Search reporter id from ticket table
     *
     * @param string           $searchQuery
     * @param array            $conditions
     * @param PagingProperties $pagingProperties
     * @param null             $callback
     *
     * @return \Diamante\DeskBundle\Entity\Ticket[]
     */
    public function search($searchQuery, array $conditions, PagingProperties $pagingProperties, $callback = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $orderByField = sprintf('%s.%s', self::SELECT_ALIAS, $pagingProperties->getSort());
        $offset = ($pagingProperties->getPage() - 1) * $pagingProperties->getLimit();

        $qb->select(self::SELECT_ALIAS)->from($this->_entityName, self::SELECT_ALIAS);

        foreach ($conditions as $condition) {
            $whereExpression = $this->buildWhereExpression($qb, $condition);
            $qb->andWhere($whereExpression);
        }

        $qb->addOrderBy($orderByField, $pagingProperties->getOrder());
        $qb->setFirstResult($offset);
        $qb->setMaxResults($pagingProperties->getLimit());

        $literal = $qb->expr()->literal("%{$searchQuery}%");
        $whereExpression = $qb->expr()->orX(
            $qb->expr()->like(sprintf('%s.%s', self::SELECT_ALIAS, 'description'), $literal),
            $qb->expr()->like(sprintf('%s.%s', self::SELECT_ALIAS, 'subject'), $literal)
        );
        $qb->andWhere($whereExpression);

        if (is_callable($callback)) {
            call_user_func_array($callback, ['qb' => $qb, 'entityName' => $this->_entityName]);
        }

        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_OBJECT);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param array            $conditions
     * @param PagingProperties $pagingProperties
     * @param null             $callback
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function filter(array &$conditions, PagingProperties $pagingProperties, $callback = null)
    {
        if ('key' == $pagingProperties->getSort()) {
            $qb = $this->orderByTicketKey($conditions, $pagingProperties);
        } else {
            $qb = $this->createFilterQuery($conditions, $pagingProperties);
        }

        if (is_callable($callback)) {
            $conditions = call_user_func_array($callback, ['qb' => $qb]);
        }


        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_OBJECT);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param array $criteria
     * @param null  $searchQuery
     * @param null  $callback
     *
     * @return int
     */
    public function count(array $criteria = [], $searchQuery = null, $callback = null)
    {
        if ($searchQuery) {
            return $this->countBySearchQuery($searchQuery, $callback);
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(t.id)')->from('DiamanteDeskBundle:Ticket', 't');

        foreach ($criteria as $condition) {
            $whereExpression = $this->buildWhereExpression($qb, $condition);
            $qb->orWhere($whereExpression);
        }

        if (is_callable($callback)) {
            call_user_func_array($callback, ['qb' => $qb, 'entityName' => $this->_entityName]);
        }

        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_SINGLE_SCALAR);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param string $searchQuery
     * @param null   $countCallback
     *
     * @return int
     */
    public function countBySearchQuery($searchQuery, $countCallback = null)
    {
        $result = $this->search($searchQuery, [], new FilterPagingProperties(null, PHP_INT_MAX), $countCallback);

        return count($result);
    }

    /**
     * @param array            $conditions
     * @param PagingProperties $pagingProperties
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function orderByTicketKey(array $conditions, PagingProperties $pagingProperties)
    {
        $qb = $this->_em->createQueryBuilder();
        $offset = ($pagingProperties->getPage() - 1) * $pagingProperties->getLimit();

        $qb->select(self::SELECT_ALIAS)
            ->addSelect('CONCAT(b.key, \'-\', ' . self::SELECT_ALIAS . '.sequenceNumber) AS HIDDEN ticketKey')
            ->from('DiamanteDeskBundle:Ticket', self::SELECT_ALIAS)
            ->join(self::SELECT_ALIAS . '.branch', 'b');

        foreach ($conditions as $condition) {
            $whereExpression = $this->buildWhereExpression($qb, $condition);
            $qb->andWhere($whereExpression);
        }

        $qb->addOrderBy('ticketKey', $pagingProperties->getOrder());
        $qb->setFirstResult($offset);
        $qb->setMaxResults($pagingProperties->getLimit());

        return $qb;
    }

    /**
     * Sorting the mysql result.
     * Implemented because Doctrine ORM not support select from subQuery
     *
     * @param $result
     *
     * @return array
     */
    public function sortByKey($result)
    {
        if (!$result || empty($result)) {
            return $result;
        }

        foreach ($result as $item) {
            $resultArray[$item->getKey()] = $item;
        }

        asort($resultArray, SORT_NATURAL);

        return array_values($resultArray);
    }

    protected function getWatchedTicketsIds(User $user)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('IDENTITY(w.ticket)')
            ->from('DiamanteDeskBundle:WatcherList', 'w')
            ->andWhere('w.userType = :user')
            ->setParameter('user', (string)$user);

        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_SCALAR);
            $result = array_map(
                function ($item) {
                    return (int)current($item);
                },
                $result
            );
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

}
