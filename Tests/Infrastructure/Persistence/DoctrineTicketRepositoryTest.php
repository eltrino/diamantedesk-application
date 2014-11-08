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
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Ticket;
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

    /**
     * @var \Doctrine\ORM\QueryBuilder
     * @Mock \Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->classMetadata->name = self::CLASS_NAME;
        $this->repository = new DoctrineTicketRepository($this->em, $this->classMetadata);
    }

    public function testGetByBranchKeyAndTicketNumber()
    {
        $branchKey = 'DB';
        $ticketNumber = 123;

        $dql = "SELECT t FROM DiamanteDeskBundle:Ticket t, DiamanteDeskBundle:Branch b
                WHERE b.key = :branchKey AND t.number = :ticketNumber";

        $branch = new Branch('Dumy Branch', 'Description');
        $ticket = new Ticket('Subject', 'Description', $branch, new User(), new User(), Source::WEB);

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
                $this->arrayHasKey('ticketNumber'),
                $this->callback(function($parameters) use ($branchKey, $ticketNumber) {
                    return $parameters['branchKey'] == $branchKey && $parameters['ticketNumber'] == $ticketNumber;
                })
            ));
        $query->expects($this->once())->method('setMaxResults')->with(1);

        $query->expects($this->once())->method('getSingleResult')->will($this->returnValue($ticket));

        $result = $this->repository->getByBranchKeyAndTicketNumber($branchKey, $ticketNumber);

        $this->assertNotNull($result);
        $this->assertEquals($ticket, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testThatTransactionRollbacksIfExceptionThrow()
    {
        $branch = new Branch('Dumy Branch', 'Description');
        $ticket = new Ticket('Subject', 'Description', $branch, new User(), new User(), Source::WEB);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('persist')->with($ticket);
        $this->em->expects($this->once())->method('flush')->will($this->throwException(new \Exception()));
        $this->em->expects($this->once())->method('rollback');

        $this->repository->store($ticket);
    }

    public function testStoreNewTicket()
    {
        $ticketCounter = Branch::TICKET_COUNTER_START_VALUE + 1;

        $branchId = 1;
        $ticketId = 2;

        $branch = new BranchStub('Dumy Branch', 'Description');
        $ticket = new TicketStub('Subject', 'Description', $branch, new User(), new User(), Source::WEB);
        $branch->setId($branchId);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('persist')->with($ticket)->will($this->returnCallback(
            function($ticketStub) use($ticketId) {
                $ticketStub->setId($ticketId);
            }
        ));
        $this->em->expects($this->once())->method('flush');

        $query = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMockForAbstractClass();

        $this->em->expects($this->exactly(2))->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(0))->method('update')
            ->with('DiamanteDeskBundle:Branch', 'b')->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(1))->method('set')
            ->with('b.ticketCounter', $ticketCounter)->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(2))->method('where')
            ->with('b.id = :branchId')->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(3))->method('setParameter')
            ->with('branchId', $branchId)->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(4))->method('getQuery')
            ->with()->will($this->returnValue($query));

        $query->expects($this->at(0))->method('execute');

        $this->queryBuilder->expects($this->at(5))->method('update')
            ->with('DiamanteDeskBundle:Ticket', 't')->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(6))->method('set')
            ->with('t.number', $ticketCounter)->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(7))->method('where')
            ->with('t.id = :ticketId')->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(8))->method('setParameter')
            ->with('ticketId', $ticketId)->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder->expects($this->at(9))->method('getQuery')
            ->with()->will($this->returnValue($query));

        $query->expects($this->at(1))->method('execute');

        $this->em->expects($this->at(5))->method('refresh')->with($branch);
        $this->em->expects($this->at(6))->method('refresh')->with($ticket);

        $this->em->expects($this->once())->method('commit');

        $this->repository->store($ticket);
    }

    public function testUpdateTicketAndSkipUpdateTicketCounter()
    {
        $branchId = 1;
        $ticketId = 2;

        $branch = new BranchStub('Dumy Branch', 'Description');
        $ticket = new TicketStub('Subject', 'Description', $branch, new User(), new User(), Source::WEB);
        $branch->setId($branchId);
        $ticket->setId($ticketId);

        $this->em->expects($this->once())->method('beginTransaction');
        $this->em->expects($this->once())->method('persist')->with($ticket);
        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->once())->method('commit');

        $this->repository->store($ticket);
    }
} 
