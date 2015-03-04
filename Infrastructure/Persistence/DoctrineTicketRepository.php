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
use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Infrastructure\User\UserStateServiceImpl;

class DoctrineTicketRepository extends DoctrineGenericRepository implements TicketRepository
{
    private $userState;

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
            ->join('t.comments', 'c')
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
            $queryBuilder->andWhere('c.private = false');
        }

        $ticket = $queryBuilder->getQuery()->getSingleResult();

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
            ->join('t.comments', 'c')
            ->where('t.uniqueId = :uniqueId')
            ->setParameter('uniqueId', $uniqueId);

        if (!$this->userState->isOroUser()) {
            $queryBuilder->andWhere('c.private = false');
        }

        return $queryBuilder->getQuery()->getSingleResult();
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
     * @param $id
     *
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function get($id)
    {
        $queryBuilder = $this->_em
            ->createQueryBuilder()->select(array('t', 'c'))
            ->from('DiamanteDeskBundle:Ticket', 't')
            ->join('t.comments', 'c')
            ->where('t.id = :id')
            ->setParameter('id', $id);

        if (!$this->userState->isOroUser()) {
            $queryBuilder->andWhere('c.private = false');
        }

        return $queryBuilder->getQuery()->getSingleResult();
    }

    /**
     * @param UserStateServiceImpl $userState
     */
    public function setUserState(UserStateServiceImpl $userState)
    {
        $this->userState = $userState;
    }
}
