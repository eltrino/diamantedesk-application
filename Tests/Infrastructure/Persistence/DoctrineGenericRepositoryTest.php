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
namespace Eltrino\DiamanteDeskBundle\Tests\Infrastructure\Persistence;

use Eltrino\DiamanteDeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DoctrineGenericRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineGenericRepository
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
        $this->classMetadata->name = self::CLASS_NAME;
        $this->repository = new DoctrineGenericRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatGets()
    {
        $id = 1;
        $entity = new EntityStub();

        $this
            ->em
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo(self::CLASS_NAME), $this->equalTo($id), $this->equalTo(0), $this->equalTo(null))
            ->will($this->returnValue($entity))
        ;

        $retrievedEntity = $this->repository->get($id);

        $this->assertEquals($entity, $retrievedEntity);
    }

    /**
     * @test
     */
    public function thatGetsAll()
    {
        $entities = array(new EntityStub(), new EntityStub());

        $this
            ->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork))
        ;
        $this
            ->unitOfWork
            ->expects($this->once())
            ->method('getEntityPersister')
            ->with($this->equalTo(self::CLASS_NAME))
            ->will($this->returnValue($this->entityPersister))
        ;
        $this
            ->entityPersister
            ->expects($this->once())
            ->method('loadAll')
            ->with(
                $this->equalTo(array()), $this->equalTo(null),
                $this->equalTo(null), $this->equalTo(null)
            )
            ->will($this->returnValue($entities))
        ;

        $retrievedEntities = $this->repository->getAll();

        $this->assertEquals($entities, $retrievedEntities);
    }

    /**
     * @test
     */
    public function thatStores()
    {
        $entity = new EntityStub();
        $this
            ->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($entity))
        ;
        $this
            ->em
            ->expects($this->once())
            ->method('flush')
        ;

        $this->repository->store($entity);
    }

    /**
     * @test
     */
    public function thatRemoves()
    {
        $entity = new EntityStub();
        $this
            ->em
            ->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($entity))
        ;
        $this
            ->em
            ->expects($this->once())
            ->method('flush')
        ;

        $this->repository->remove($entity);
    }
}
