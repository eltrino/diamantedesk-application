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
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\DeskBundle\Model\Shared\FilterableRepository;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Shared\Repository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DoctrineGenericRepository
 *
 * @package Diamante\DeskBundle\Infrastructure\Persistence
 *
 * @method Ticket findOneByTicketKey(TicketKey $key)
 */
class DoctrineGenericRepository extends EntityRepository implements FilterableRepository, Repository
{
    const SELECT_ALIAS = 'e';

    const HAS_TABLE_ALIAS = 4;
    /**
     * @param $id
     * @return Entity|null
     */
    public function get($id)
    {
        /** @var Entity|null $ticket */
        $ticket = $this->find($id);
        return $ticket;
    }

    /**
     * @return Entity[]
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * Store Entity
     *
     * @param Entity $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function store(Entity $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush($entity);
    }

    /**
     * @param Entity $entity
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Entity $entity)
    {
        $this->_em->remove($entity);
        // $this->clearSearchIndex($entity); TODO: Temp. commented unless method will be fixed
        $this->_em->flush($entity);
    }

    /**
     * @param array $conditions
     * @param PagingProperties $pagingProperties
     * @param null $callback
     * @return Collection|static
     */
    public function filter(array &$conditions, PagingProperties $pagingProperties, $callback = null)
    {
        $qb = $this->createFilterQuery($conditions, $pagingProperties);
        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_OBJECT);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param array $conditions
     * @param PagingProperties $pagingProperties
     * @return QueryBuilder
     */
    protected function createFilterQuery(array $conditions, PagingProperties $pagingProperties)
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

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param array $condition
     * @return Query\Expr
     */
    protected function buildWhereExpression(QueryBuilder $qb, array $condition)
    {
        if(self::HAS_TABLE_ALIAS == count($condition)) {
            list($table, $field, $operator, $value) = $condition;
        } else {
            $table = self::SELECT_ALIAS;
            list($field, $operator, $value) = $condition;
        }

        $field = sprintf('%s.%s', $table, $field);

        switch ($operator) {
            case 'like':
                $literal = $qb->expr()->literal(sprintf('%%s%', $value));
                break;
            default:
                $literal = $qb->expr()->literal(sprintf('%s', $value));
                break;
        }

        $expr = call_user_func_array(array($qb->expr(), $operator), array($field, $literal));

        return $expr;
    }

    /**
     * @param array $criteria
     * @param null $searchQuery
     * @param null $callback
     * @return int
     */
    public function count(array $criteria = [], $searchQuery = null, $callback = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select($qb->expr()->count(self::SELECT_ALIAS))->from($this->_entityName, self::SELECT_ALIAS);

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
     * Refresh search indexes to prevent transaction rollback
     *
     * @param Entity $entity
     * @throws ORMException
     */
    public function clearSearchIndex(Entity $entity)
    {
        $searchRepository = $this->_em->getRepository('OroSearchBundle:Item');
        if (!$searchRepository) {
            return;
        }
        $searchItems = $searchRepository->findBy(
            ['entity' => get_class($entity), 'recordId' => $entity->getId()]
        );

        foreach ($searchItems as $item) {
            $this->_em->remove($item);
        }

    }
}
