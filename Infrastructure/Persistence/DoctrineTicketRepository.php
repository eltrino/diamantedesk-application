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
use Diamante\UserBundle\Api\Internal\UserStateServiceImpl;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\User;
use Doctrine\ORM\Query;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;

class DoctrineTicketRepository extends DoctrineGenericRepository implements TicketRepository
{
    /**
     * @var UserStateServiceImpl
     */
    private $userState;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;


    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

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
            ->createQueryBuilder()->select(array('t', 'c'))
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->from('DiamanteDeskBundle:Branch', 'b')
            ->leftJoin('t.comments', 'c')
            ->where('b.id = t.branch')
            ->andWhere('b.key = :branchKey')
            ->andWhere('t.sequenceNumber = :ticketSequenceNumber')
            ->setParameters(
                array(
                    'branchKey'            => $key->getBranchKey(),
                    'ticketSequenceNumber' => $key->getTicketSequenceNumber()
                )
            );

        if (!$this->userState->isOroUser()) {
            $queryBuilder->andWhere('c.private is null or c.private = false');
        }

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
            ->createQueryBuilder()->select(array('t', 'c'))
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->leftJoin('t.comments', 'c')
            ->where('t.uniqueId = :uniqueId')
            ->setParameter('uniqueId', $uniqueId);

        if (!$this->userState->isOroUser()) {
            $queryBuilder->andWhere('c.private is null or c.private = false');
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
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
        $queryBuilder = $this->_em
            ->createQueryBuilder()->select(array('t', 'c'))
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->leftJoin('t.comments', 'c')
            ->where('t.id = :id')
            ->setParameter('id', $id);

        if (!$this->userState->isOroUser()) {
            $queryBuilder->andWhere('c.private is null or c.private = false');
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $conditions
     * @param PagingProperties $pagingProperties
     * @return \Doctrine\Common\Collections\Collection|static
     * @throws \Exception
     */
    public function filter(array $conditions, PagingProperties $pagingProperties)
    {
        $qb = $this->createFilterQuery($conditions, $pagingProperties);

        $user = $this->securityContext->getToken()->getUser();
        if ($user instanceof ApiUser) {
            $email = $user->getEmail();
            $diamanteUser = $this->diamanteUserRepository->findUserByEmail($email);
            $user = new User($diamanteUser->getId(), User::TYPE_DIAMANTE);
            $qb->andWhere(self::SELECT_ALIAS . '.reporter = :reporter')
                ->setParameter('reporter', $user);
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
     * @param UserStateServiceImpl $userState
     */
    public function setUserState(UserStateServiceImpl $userState)
    {
        $this->userState = $userState;
    }

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @param DiamanteUserRepository $diamanteUserRepository
     */
    public function setDiamanteUserRepository(DiamanteUserRepository $diamanteUserRepository)
    {
        $this->diamanteUserRepository = $diamanteUserRepository;
    }
}
