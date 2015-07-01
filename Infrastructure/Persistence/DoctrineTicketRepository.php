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

use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\DiamanteUser;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Query;

class DoctrineTicketRepository extends DoctrineGenericRepository implements TicketRepository
{

    /**
     * Find Ticket by given TicketKey
     *
     * @param TicketKey $key
     *
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
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
                array(
                    'branchKey'            => $key->getBranchKey(),
                    'ticketSequenceNumber' => $key->getTicketSequenceNumber()
                )
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
            array(
                'reporter_id' => (string)$user,
            )
        );
        $query->execute();
    }

    /**
     * Search reporter id from ticket table
     * @param string $searchQuery
     * @param array $conditions
     * @param PagingProperties $pagingProperties
     * @return \Diamante\DeskBundle\Entity\Ticket[]
     */
    public function search($searchQuery, array $conditions, PagingProperties $pagingProperties)
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

        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_OBJECT);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param $id
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function get($id)
    {
        $ticket = $this->_em
            ->createQueryBuilder()->select('t')
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->where('t.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        return $ticket;
    }

    /**
     * @param array $conditions
     * @param PagingProperties $pagingProperties
     * @param ApiUser $user
     * @return \Doctrine\Common\Collections\Collection|static
     * @throws \Exception
     */
    public function filter(array &$conditions, PagingProperties $pagingProperties, $user = null)
    {
        if('key' == $pagingProperties->getSort()) {
            $qb = $this->orderByTicketKey($conditions, $pagingProperties);
        } else {
            $qb = $this->createFilterQuery($conditions, $pagingProperties);
        }

        if ($user instanceof DiamanteUser) {
            $user = new User($user->getId(), User::TYPE_DIAMANTE);
            $qb->addSelect('w')
                ->join(self::SELECT_ALIAS . '.watcherList', 'w')
                ->andWhere('w.userType = :watcher')
                ->setParameter('watcher', $user);
            $conditions[] = ['w', 'userType', 'eq', $user];
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
     * @return int
     */
    public function count(array $criteria)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select($qb->expr()->count(self::SELECT_ALIAS))
            ->from($this->_entityName, self::SELECT_ALIAS)
            ->join(self::SELECT_ALIAS . '.watcherList', 'w');

        foreach ($criteria as $condition) {
            $whereExpression = $this->buildWhereExpression($qb, $condition);
            $qb->orWhere($whereExpression);
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
     * @param array $conditions
     * @param PagingProperties $pagingProperties
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
     * @param PagingProperties $pagingProperties
     * @return array
     */
    public function sortByKey($result, PagingProperties $pagingProperties)
    {
        if (!$result || empty($result)) {
            return $result;
        }

        usort($result,
            function ($a, $b) use ($pagingProperties) {

                $reflectionA = new \ReflectionClass($a);
                $reflectionB = new \ReflectionClass($b);

                $sortBy = $pagingProperties->getSort();
                $orderBy = $pagingProperties->getOrder();

                if (!$reflectionA->getProperty($sortBy) || !$reflectionB->getProperty($sortBy)) {
                    return 0;
                }

                $propertyA = $reflectionA->getProperty($sortBy);
                $propertyB = $reflectionB->getProperty($sortBy);

                $propertyA->setAccessible(true);
                $propertyB->setAccessible(true);

                $valueA = (string)$a->getKey();
                $valueB = (string)$b->getKey();

                if (is_string($valueA) || is_string($valueB)) {

                    $sortableArray = array($valueA, $valueB);
                    $originalSortableArray = $sortableArray;

                    asort($sortableArray);

                    if ($orderBy == 'desc') {
                        return $sortableArray !== $originalSortableArray;
                    } else {
                        return $sortableArray === $originalSortableArray;
                    }
                }

                return 0;
            }
        );

        return $result;
    }

}
