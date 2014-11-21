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
    const TICKET_SEQUENCE_NUMBER_FIELD = 'sequenceNumber';

    /**
     * @ORM\PrePersist
     * @param Ticket $ticket
     * @param LifecycleEventArgs $event
     */
    public function prePersistHandler(Ticket $ticket, LifecycleEventArgs $event)
    {
        $em = $event->getEntityManager();
        $query = $em->createQuery("SELECT MAX(t.sequenceNumber) FROM DiamanteDeskBundle:Ticket t WHERE t.branch = :branchId")
            ->setParameter('branchId', $ticket->getBranch()->getId());
        $lastTicketSequenceNumber = $query->getSingleScalarResult();

        $class = $em->getClassMetadata(get_class($ticket));
        $lastTicketSequenceNumber++;
        $class->setFieldValue(
            $ticket, $class->getFieldName(self::TICKET_SEQUENCE_NUMBER_FIELD), new TicketSequenceNumber($lastTicketSequenceNumber)
        );
    }
} 
