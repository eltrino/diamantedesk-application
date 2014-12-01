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
namespace Diamante\DeskBundle\Tests\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\TicketListener;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\User\User;

class TicketListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TicketListener
     */
    private $listener;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     * @Mock \Doctrine\ORM\EntityManagerInterface
     */
    private $objectManager;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     * @Mock \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $classMetadata;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->listener = new TicketListener();
    }

    public function testPrePersistHandler()
    {
        $branchId = 1;
        $lastTicketSequenceNumberValue = 9;
        $ticketSequenceNumberFieldName = 'number';
        $branch = new BranchStub('DB', 'Dummy Branch', 'Desc');
        $branch->setId($branchId);
        $reporter = new User(1, User::TYPE_DIAMANTE);

        $ticket = new Ticket(
            new TicketSequenceNumber(null),
            'Subject',
            'Description',
            $branch,
            $reporter,
            new OroUser(),
            new Source(Source::WEB),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::NEW_ONE)
        );
        $event = new LifecycleEventArgs($ticket, $this->objectManager);

        $dqlQueryStr = "SELECT MAX(t.sequenceNumber) FROM DiamanteDeskBundle:Ticket t WHERE t.branch = :branchId";

        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('setParameter', 'getSingleScalarResult'))
            ->getMockForAbstractClass();

        $this->objectManager->expects($this->once())->method('createQuery')->with($dqlQueryStr)
            ->will($this->returnValue($query));
        $query->expects($this->once())->method('setParameter')->with('branchId', $branchId)
            ->will($this->returnValue($query));
        $query->expects($this->once())->method('getSingleScalarResult')->will($this->returnValue($lastTicketSequenceNumberValue));

        $this->objectManager->expects($this->once())->method('getClassMetadata')->with(get_class($ticket))
            ->will($this->returnValue($this->classMetadata));

        $this->classMetadata->expects($this->once())->method('getFieldName')->with(TicketListener::TICKET_SEQUENCE_NUMBER_FIELD)
            ->will($this->returnValue($ticketSequenceNumberFieldName));
        $this->classMetadata->expects($this->once())->method('setFieldValue')
            ->with($ticket, $ticketSequenceNumberFieldName, $this->logicalAnd(
                        $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber'),
                        $this->callback(
                            function(TicketSequenceNumber $other) use ($lastTicketSequenceNumberValue) {
                                return $other->getValue() == $lastTicketSequenceNumberValue + 1;
                            })
                ));

        $this->listener->prePersistHandler($ticket, $event);
    }
} 
