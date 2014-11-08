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

use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Ticket\TicketRepository;

class DoctrineTicketRepository extends DoctrineGenericRepository implements TicketRepository
{
    /**
     * Find Ticket by given Branch key and Ticket number
     * @param string $branchKey
     * @param int $ticketNumber
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByBranchKeyAndTicketNumber($branchKey, $ticketNumber)
    {
        $query = $this->_em
            ->createQuery("SELECT t FROM DiamanteDeskBundle:Ticket t, DiamanteDeskBundle:Branch b
                WHERE b.key = :branchKey AND t.number = :ticketNumber");
        $query->setParameters(array(
                'branchKey' => $branchKey,
                'ticketNumber' => $ticketNumber
            ));
        $query->setMaxResults(1);

        $ticket = $query->getSingleResult();
        return $ticket;
    }

    /**
     * Store Ticket. If Ticket is new - update ticket counter and assign new ticket number
     * @param Entity $entity
     * @throws \Exception
     */
    public function store(Entity $entity)
    {
        $this->_em->beginTransaction();

        $isNewTicket = true;
        if ($entity->getId()) {
            $isNewTicket = false;
        }
        try {

            parent::store($entity);

            if ($isNewTicket) {// update ticket counter

                $ticketCounter = $entity->getBranch()->getTicketCounter() + 1;

                $this->updateBranchTicketCounter($ticketCounter, $entity->getBranch()->getId());
                $this->updateTicketNumber($ticketCounter, $entity->getId());

                $this->_em->refresh($entity->getBranch());
                $this->_em->refresh($entity);
            }

            $this->_em->commit();
        } catch (\Exception $e) {
            $this->_em->rollback();
            throw $e;
        }
    }

    /**
     * @param int $counter
     * @param int $branchId
     * @return void
     */
    private function updateBranchTicketCounter($counter, $branchId)
    {
        $builder = $this->_em->createQueryBuilder();
        $builder->update('DiamanteDeskBundle:Branch', 'b')
            ->set('b.ticketCounter', $counter)
            ->where('b.id = :branchId')
            ->setParameter('branchId', $branchId);
        $builder->getQuery()->execute();
    }

    /**
     * @param int $number
     * @param int $ticketId
     * @return void
     */
    private function updateTicketNumber($number, $ticketId)
    {
        $builder = $this->_em->createQueryBuilder();
        $builder->update('DiamanteDeskBundle:Ticket', 't')
            ->set('t.number', $number)
            ->where('t.id = :ticketId')
            ->setParameter('ticketId', $ticketId);
        $builder->getQuery()->execute();
    }
} 
