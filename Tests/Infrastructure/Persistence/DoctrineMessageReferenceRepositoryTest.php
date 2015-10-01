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

use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\DeskBundle\Model\Ticket\Status;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineMessageReferenceRepository;

class DoctrineMessageReferenceRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineMessageReferenceRepository
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
     * @var \Doctrine\ORM\UnitOfWork
     * @Mock \Doctrine\ORM\UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var \Doctrine\ORM\Persisters\Entity\BasicEntityPersister
     * @Mock \Doctrine\ORM\Persisters\Entity\BasicEntityPersister
     */
    private $entityPersister;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->classMetadata->name = self::DUMMY_CLASS_NAME;
        $this->repository = new DoctrineMessageReferenceRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatMessageReferenceRetrievesByMessageId()
    {
        $messageId = 1;
        $messageReference = $this->getMessageReference();

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork->expects($this->once())
            ->method('getEntityPersister')
            ->with($this->equalTo(self::DUMMY_CLASS_NAME))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister->expects($this->once())
            ->method('load')
            ->with(
                $this->equalTo(array('messageId' => $messageId)), $this->equalTo(null), $this->equalTo(null), array(), $this->equalTo(0),
                $this->equalTo(1), $this->equalTo(null)
            )->will($this->returnValue($messageReference));

        $retrievedMessageReference = $this->repository->getReferenceByMessageId($messageId);

        $this->assertNotNull($retrievedMessageReference);
        $this->assertEquals($messageReference, $retrievedMessageReference);
    }

    public function testFindAllByTicket()
    {
        $messageReference = $this->getMessageReference();
        $ticket = $messageReference->getTicket();
        $references = array($messageReference);

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork->expects($this->once())
            ->method('getEntityPersister')
            ->with($this->equalTo(self::DUMMY_CLASS_NAME))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister->expects($this->once())
            ->method('loadAll')
            ->with($this->equalTo(array('ticket' => $ticket)), null, null, null)
            ->will($this->returnValue($references));

        $result = $this->repository->findAllByTicket($ticket);

        $this->assertEquals($references, $result);
    }

    private function getMessageReference()
    {
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(null),
            'Subject',
            'Description',
            new Branch('DUMM', 'DUMMY_NAME', 'DUMMY_DESCR'),
            new User(1, User::TYPE_DIAMANTE),
            new OroUser(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::OPEN)
        );

        return new MessageReference(
            'dummmy_message_id', $ticket
        );
    }
}
