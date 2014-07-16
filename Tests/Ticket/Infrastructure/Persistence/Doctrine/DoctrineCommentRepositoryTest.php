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
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Oro\Bundle\UserBundle\Entity\User;
use Eltrino\DiamanteDeskBundle\Entity\Comment;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Persistence\Doctrine\DoctrineCommentRepository;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DoctrineCommentRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineCommentRepository
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
        $this->repository = new DoctrineCommentRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatCommentStores()
    {
        $comment = $this->comment();
        $this->em->expects($this->once())->method('persist')->with($this->equalTo($comment));
        $this->em->expects($this->once())->method('flush');

        $this->repository->store($comment);
    }

    /**
     * @test
     */
    public function thatCommentRemoves()
    {
        $comment = $this->comment();
        $this->em->expects($this->once())->method('remove')->with($this->equalTo($comment));
        $this->em->expects($this->once())->method('flush');

        $this->repository->remove($comment);
    }

    /**
     * @test
     */
    public function thatCommentRetrievesByGivenId()
    {
        $commentId = 1;
        $comment = $this->comment();
        $this->em->expects($this->once())->method('find')->with(
            $this->equalTo(self::DUMMY_CLASS_NAME), $this->equalTo($commentId),
            $this->equalTo(LockMode::NONE), $this->equalTo(null)
        )->will($this->returnValue($comment));

        $retrievedComment = $this->repository->find($commentId);

        $this->assertNotNull($retrievedComment);
        $this->assertEquals($comment, $retrievedComment);
    }

    /**
     * @test
     */
    public function thatRetrievesAllComments()
    {
        $comments = array($this->comment(), $this->comment());
        $this->em->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($this->unitOfWork));
        $this->unitOfWork->expects($this->once())->method('getEntityPersister')->with($this->equalTo(self::DUMMY_CLASS_NAME))
            ->will($this->returnValue($this->entityPersister));
        $this->entityPersister->expects($this->once())->method('loadAll')->with(
            $this->equalTo(array()), $this->equalTo(null), $this->equalTo(null), $this->equalTo(null)
        )->will($this->returnValue($comments));

        $retrievedComments = $this->repository->getAll();

        $this->assertNotEmpty($retrievedComments);
        $this->assertEquals($comments, $retrievedComments);
    }

    private function comment()
    {
        return new Comment(
            'Content',
            new Ticket(
                'Subject',
                'Description',
                new Branch('DUMMY_NAME', 'DUMMY_DESCR'),
                new User(),
                new User(),
                Status::NEW_ONE
            ),
            'author');
    }
}
