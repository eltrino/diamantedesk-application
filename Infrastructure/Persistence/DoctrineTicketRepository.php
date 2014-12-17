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

class DoctrineTicketRepository extends DoctrineGenericRepository implements TicketRepository
{
    /**
     * Find Ticket by given TicketKey
     * @param TicketKey $key
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByTicketKey(TicketKey $key)
    {
        $query = $this->_em
            ->createQuery("SELECT t FROM DiamanteDeskBundle:Ticket t, DiamanteDeskBundle:Branch b
                WHERE b.id = t.branch AND b.key = :branchKey AND t.sequenceNumber = :ticketSequenceNumber");
        $query->setParameters(array(
                'branchKey' => $key->getBranchKey(),
                'ticketSequenceNumber' => $key->getTicketSequenceNumber()
            ));
        $query->setMaxResults(1);

        $ticket = $query->getSingleResult();
        return $ticket;
    }

    /**
     * @param UniqueId $uniqueId
     * @return \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    public function getByUniqueId(UniqueId $uniqueId)
    {
        return $this->findOneBy(array('uniqueId' => $uniqueId));
    }
}
