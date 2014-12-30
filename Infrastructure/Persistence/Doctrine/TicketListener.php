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
namespace Diamante\DeskBundle\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

class TicketListener
{
    const TICKET_SEQUENCE_NUMBER_FIELD_TO_UPDATE = 'number';

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @param Ticket $ticket
     * @param LifecycleEventArgs $event
     */
    public function prePersistHandler(Ticket $ticket, LifecycleEventArgs $event)
    {
        if ($ticket->getSequenceNumber()->getValue()) {
            return;
        }
        $em = $event->getEntityManager();
        $query = $em->createQuery("SELECT MAX(t.sequenceNumber) FROM DiamanteDeskBundle:Ticket t WHERE t.branch = :branchId")
            ->setParameter('branchId', $ticket->getBranch()->getId());
        $ticketSequenceNumberValue = $query->getSingleScalarResult();
        $ticketSequenceNumberValue++;
        $ticketSequenceNumber = $ticket->getSequenceNumber();

        $ref = new \ReflectionClass($ticketSequenceNumber);
        $property = $ref->getProperty(self::TICKET_SEQUENCE_NUMBER_FIELD_TO_UPDATE);
        $property->setAccessible(true);
        $property->setValue($ticketSequenceNumber, $ticketSequenceNumberValue);
        $property->setAccessible(false);
    }
}
