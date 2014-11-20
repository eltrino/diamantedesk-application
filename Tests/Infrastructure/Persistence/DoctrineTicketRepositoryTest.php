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
namespace Diamante\DeskBundle\Tests\Infrastructure\Persistence;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketRepository;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class DoctrineTicketRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineTicketRepository;
     */
    private $repository;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     * @Mock \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $classMetadata;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->classMetadata->name = self::CLASS_NAME;
        $this->repository = new DoctrineTicketRepository($this->em, $this->classMetadata);
    }

    public function testGetByTicketKey()
    {
        $branchKey = 'DB';
        $ticketSequenceNumber = 123;

        $dql = "SELECT t FROM DiamanteDeskBundle:Ticket t, DiamanteDeskBundle:Branch b
                WHERE b.id = t.branch AND b.key = :branchKey AND t.sequenceNumber = :ticketSequenceNumber";

        $branch = new Branch('DB', 'Dummy Branch', 'Description');
        $ticket = new Ticket(
            new TicketSequenceNumber($ticketSequenceNumber),
            'Subject',
            'Description',
            $branch,
            new User(),
            new User(),
            new Source(Source::WEB),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::NEW_ONE)
        );

        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('setParameters', 'getSingleResult', 'setMaxResults'))
            ->getMockForAbstractClass();

        $this->em->expects($this->once())
            ->method('createQuery')
            ->with($this->logicalAnd(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->stringContains($dql)
            ))
            ->will($this->returnValue($query));

        $query->expects($this->once())->method('setParameters')
            ->with($this->logicalAnd(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY),
                $this->arrayHasKey('branchKey'),
                $this->arrayHasKey('ticketSequenceNumber'),
                $this->callback(function($parameters) use ($branchKey, $ticketSequenceNumber) {
                    return $parameters['branchKey'] == $branchKey
                            && $parameters['ticketSequenceNumber'] == $ticketSequenceNumber;
                })
            ));
        $query->expects($this->once())->method('setMaxResults')->with(1);

        $query->expects($this->once())->method('getSingleResult')->will($this->returnValue($ticket));

        $result = $this->repository->getByTicketKey(new TicketKey($branchKey, $ticketSequenceNumber));

        $this->assertNotNull($result);
        $this->assertEquals($ticket, $result);
    }
} 
