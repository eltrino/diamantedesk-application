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

use Diamante\DeskBundle\Model\Ticket\CommentRepository;
use Diamante\UserBundle\Api\Internal\UserStateServiceImpl;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Doctrine\ORM\Query;

class DoctrineCommentRepository extends DoctrineGenericRepository implements CommentRepository
{
    /**
     * @var UserStateServiceImpl
     */
    private $userState;

    /**
     * @param $id
     * @return Entity
     */
    public function get($id)
    {
        $comment = $this->find($id);

        if (is_null($comment) || !$this->userState->isOroUser() && $comment->isPrivate()) {
            throw new \RuntimeException('Comment loading failed, comment not found.');
        }

        return $comment;
    }

    /**
     * @return Entity[]
     */
    public function getAll()
    {
        $queryBuilder = $this->_em
            ->createQueryBuilder()->select(array('c'))
            ->from('DiamanteDeskBundle:Comment', 'c');

        if (!$this->userState->isOroUser()) {
            $queryBuilder->where('c.private = false');
        }

        return $queryBuilder->getQuery()->getResult();
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
        $qb = $this->_em->createQueryBuilder();
        $orderByField = sprintf('%s.%s', self::SELECT_ALIAS, $pagingProperties->getSort());
        $offset = ($pagingProperties->getPage()-1) * $pagingProperties->getLimit();

        $qb->select(self::SELECT_ALIAS)->from($this->_entityName, self::SELECT_ALIAS);

        foreach ($conditions as $condition) {
            $whereExpression = $this->buildWhereExpression($qb, $condition);
            $qb->orWhere($whereExpression);
        }

        if (!$this->userState->isOroUser()) {
            $publicComments = sprintf('%s.private = false', self::SELECT_ALIAS);
            $qb->andWhere($publicComments);
        }

        $qb->addOrderBy($orderByField, $pagingProperties->getOrder());
        $qb->setFirstResult($offset);
        $qb->setMaxResults($pagingProperties->getLimit());

        $query = $qb->getQuery();

        try {
            $result = $query->getResult(Query::HYDRATE_OBJECT);
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * Remove author id from comment table
     * @param User $user
     */
    public function removeCommentAuthor(User $user)
    {
        $query = $this->_em
            ->createQuery("UPDATE DiamanteDeskBundle:Comment t SET t.author = null WHERE t.author = :author_id");
        $query->setParameters(array(
                'author_id' => (string)$user,
            ));
        $query->execute();
    }

    /**
     * @param UserStateServiceImpl $userState
     */
    public function setUserState(UserStateServiceImpl $userState)
    {
        $this->userState = $userState;
    }
}
