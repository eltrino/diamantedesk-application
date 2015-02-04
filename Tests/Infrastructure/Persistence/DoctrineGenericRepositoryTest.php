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

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Shared\Filter\FilterPagingProperties;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @var \Doctrine\ORM\QueryBuilder
     * @Mock \Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var \Doctrine\ORM\AbstractQuery
     */
    private $filterQuery;

    /**
     * @var \Doctrine\ORM\Query\Expr
     * @Mock \Doctrine\ORM\Query\Expr
     */
    private $expressionBuilder;

    /**
     * @var \Doctrine\ORM\Internal\Hydration\ObjectHydrator
     * @Mock \Doctrine\ORM\Internal\Hydration\ObjectHydrator
     */
    private $objectHydrator;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->filterQuery = $this->getMockForAbstractClass('\Doctrine\ORM\AbstractQuery', array($this->em));

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

    /**
     * @test
     */
    public function thatFilters()
    {
        $a = new EntityStub();
        $b = new EntityStub();
        $a->name = "DUMMY_NAME";
        $a->id = 1;
        $b->name = "NOT_DUMMY_NAME";
        $b->id = 2;

        $entities = array($a, $b);

        $pagingConfig = new FilterPagingProperties();
        $conditions = array(
            array('name', 'eq', 'DUMMY_NAME')
        );
        $whereExpr = new \Doctrine\ORM\Query\Expr\Comparison('e.name','eq',"'DUMMY_NAME'");
        $this->em
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('select')
            ->with($this->equalTo('e'))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('from')
            ->with($this->equalTo($this->classMetadata->name), $this->equalTo('e'))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->atLeastOnce())
            ->method('orWhere')
            ->with($this->equalTo($whereExpr))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('addOrderBy')
            ->with($this->equalTo('e.id'), $this->equalTo($pagingConfig->getOrder()))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('setFirstResult')
            ->with($this->equalTo(0))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('setMaxResults')
            ->with($this->equalTo($pagingConfig->getLimit()))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($this->filterQuery));

        $this->queryBuilder
            ->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($this->expressionBuilder));

        $this->em
            ->expects($this->any())
            ->method('getExpressionBuilder')
            ->will($this->returnValue($this->expressionBuilder));

        $this->expressionBuilder
            ->expects($this->atLeastOnce())
            ->method('literal')
            ->with($this->equalTo('DUMMY_NAME'))
            ->will($this->returnValue("'DUMMY_NAME'"));

        $this->expressionBuilder
            ->expects($this->atLeastOnce())
            ->method('eq')
            ->with($this->equalTo('e.name'), $this->equalTo("'DUMMY_NAME'"))
            ->will($this->returnValue($whereExpr));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('newHydrator')
            ->with($this->equalTo(Query::HYDRATE_OBJECT))
            ->will($this->returnValue($this->objectHydrator));

        $this->objectHydrator
            ->expects($this->atLeastOnce())
            ->method('hydrateAll')
            ->will($this->returnValue(array($a)));

        $filteredEntities = $this->repository->filter($conditions, $pagingConfig);

        $this->assertNotNull($filteredEntities);
        $this->assertTrue(is_array($filteredEntities));
        $this->assertNotEmpty($filteredEntities);
        $this->assertEquals($a, $filteredEntities[0]);
    }
}
