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

namespace Eltrino\DiamanteDeskBundle\Tests\Ticket\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\LockMode;
use Eltrino\DiamanteDeskBundle\Entity\MessageReference;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Priority;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Source;
use Oro\Bundle\UserBundle\Entity\User;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Persistence\Doctrine\DoctrineMessageReferenceRepository;

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
     * @var \Doctrine\ORM\Persisters\BasicEntityPersister
     * @Mock \Doctrine\ORM\Persisters\BasicEntityPersister
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
    public function thatMessageReferenceStores()
    {
        $messageReference = $this->getMessageReference();
        $this->em->expects($this->once())->method('persist')->with($this->equalTo($messageReference));
        $this->em->expects($this->once())->method('flush');

        $this->repository->store($messageReference);
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

        $retrievedMessageReference = $this->repository->findOneBy(array('messageId' => $messageId));

        $this->assertNotNull($retrievedMessageReference);
        $this->assertEquals($messageReference, $retrievedMessageReference);
    }

    private function getMessageReference()
    {
        $ticket = new Ticket(
            'Subject',
            'Description',
            new Branch('DUMMY_NAME', 'DUMMY_DESCR'),
            new User(),
            new User(),
            Source::PHONE,
            Priority::PRIORITY_MEDIUM,
            Status::OPEN
        );

        return new MessageReference(
            'dummmy_message_id', $ticket
        );
    }
} 