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

use Eltrino\DiamanteDeskBundle\Ticket\Model\Ticket;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Persistence\Doctrine\DoctrineTicketRepository;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class DoctrineTicketRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineTicketRepository
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
        $this->repository = new DoctrineTicketRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatTicketStores()
    {
        $ticket = $this->ticket();
        $this->em->expects($this->once())->method('persist')->with($this->equalTo($ticket));
        $this->em->expects($this->once())->method('flush');

        $this->repository->store($ticket);
    }

    /**
     * @test
     */
    public function thatAttachmentRemoves()
    {
        $ticket = $this->ticket();
        $this->em->expects($this->once())->method('remove')->with($this->equalTo($ticket));
        $this->em->expects($this->once())->method('flush');

        $this->repository->remove($ticket);
    }

    private function ticket()
    {
        return new Ticket(
            'Subject',
            'Description',
            new Branch('DUMMY_NAME', 'DUMMY_DESCR'),
            new User(),
            new User(),
            Status::OPEN
        );
    }
}
