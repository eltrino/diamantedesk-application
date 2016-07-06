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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketListener
{
    const TICKET_SEQUENCE_NUMBER_FIELD_TO_UPDATE = 'number';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * TicketListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     *
     * @param Ticket             $ticket
     * @param LifecycleEventArgs $event
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    public function prePersistHandler(Ticket $ticket, LifecycleEventArgs $event)
    {
        $this->reporterEmailSetter($ticket);

        if ($ticket->getSequenceNumber()->getValue()) {
            return;
        }

        $ticketSequenceNumber = $ticket->getSequenceNumber();
        $ticketSequenceNumberValue = $ticket->getBranch()->getSequenceNumber();
        $ticket->getBranch()->setSequenceNumber($ticketSequenceNumberValue + 1);

        $ref = new \ReflectionClass($ticketSequenceNumber);
        $property = $ref->getProperty(self::TICKET_SEQUENCE_NUMBER_FIELD_TO_UPDATE);
        $property->setAccessible(true);
        $property->setValue($ticketSequenceNumber, $ticketSequenceNumberValue);
        $property->setAccessible(false);
    }

    /**
     * @param Ticket $ticket
     */
    protected function reporterEmailSetter(Ticket $ticket)
    {
        $reporter = $ticket->getReporter();
        $user = $this->container->get('diamante.user.service')->getByUser($reporter);
        $ticket->setReporterEmail($user->getEmail());
    }
}
