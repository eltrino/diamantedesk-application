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
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Doctrine\ORM\Query;
use Diamante\DeskBundle\Model\Shared\Filter\FilterPagingProperties;

class DoctrineTicketRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineTicketRepository
     */
    private $repository;

    /**
     * @var DoctrineTicketRepository
     * @Mock DoctrineTicketRepository
     */
    private $ticketRepository;

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

    /**
     * @var \Doctrine\ORM\AbstractQuery
     */
    private $searchQuery;

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

        $this->searchQuery = $this->getMockForAbstractClass('\Doctrine\ORM\AbstractQuery', array($this->em));
        $this->classMetadata->name = self::CLASS_NAME;
        $this->repository = new DoctrineTicketRepository($this->em, $this->classMetadata);
    }

        public function testGetByTicketKey()
        {
            $branchKey = 'DB';
            $ticketSequenceNumber = 123;

            $branch = new Branch('DB', 'Dummy Branch', 'Description');
            $ticket = new Ticket(
                new UniqueId('unique_id'),
                new TicketSequenceNumber($ticketSequenceNumber),
                'Subject',
                'Description',
                $branch,
                new User(1, User::TYPE_DIAMANTE),
                new OroUser(),
                new Source(Source::WEB),
                new Priority(Priority::PRIORITY_MEDIUM),
                new Status(Status::NEW_ONE)
            );

            $this->em
                ->expects($this->once())
                ->method('createQueryBuilder')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('select')
                ->with($this->equalTo('t'))
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->exactly(2))
                ->method('from')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('where')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->atLeast(2))
                ->method('andWhere')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('setParameters')
                ->with(
                    [
                        'branchKey'            => $branchKey,
                        'ticketSequenceNumber' => $ticketSequenceNumber
                    ]
                )
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('getQuery')
                ->will($this->returnValue($this->searchQuery));

            $this->em
                ->expects($this->atLeastOnce())
                ->method('newHydrator')
                ->with($this->equalTo(Query::HYDRATE_OBJECT))
                ->will($this->returnValue($this->objectHydrator));

            $this->objectHydrator
                ->expects($this->atLeastOnce())
                ->method('hydrateAll')
                ->will($this->returnValue($ticket));

            $result = $this->repository->getByTicketKey(new TicketKey($branchKey, $ticketSequenceNumber));

            $this->assertNotNull($result);
            $this->assertEquals($ticket, $result);
        }

        public function testGetByTicketUniqueId()
        {
            $uniqueId = 'unique_id';
            $ticketSequenceNumber = 123;

            $branch = new Branch('DB', 'Dummy Branch', 'Description');
            $ticket = new Ticket(
                new UniqueId($uniqueId),
                new TicketSequenceNumber($ticketSequenceNumber),
                'Subject',
                'Description',
                $branch,
                new User(1, User::TYPE_DIAMANTE),
                new OroUser(),
                new Source(Source::WEB),
                new Priority(Priority::PRIORITY_MEDIUM),
                new Status(Status::NEW_ONE)
            );

            $this->em
                ->expects($this->once())
                ->method('createQueryBuilder')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('select')
                ->with($this->equalTo('t'))
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('from')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('where')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('setParameter')
                ->will($this->returnValue($this->queryBuilder));

            $this->queryBuilder
                ->expects($this->once())
                ->method('getQuery')
                ->will($this->returnValue($this->searchQuery));

            $this->em
                ->expects($this->atLeastOnce())
                ->method('newHydrator')
                ->with($this->equalTo(Query::HYDRATE_OBJECT))
                ->will($this->returnValue($this->objectHydrator));

            $this->objectHydrator
                ->expects($this->atLeastOnce())
                ->method('hydrateAll')
                ->will($this->returnValue($ticket));

            $result = $this->repository->getByUniqueId(new UniqueId($uniqueId));

            $this->assertNotNull($result);
            $this->assertEquals($ticket, $result);
        }

    public function testGetByTicketId()
    {
        $id = 1;
        $uniqueId = 'unique_id';
        $ticketSequenceNumber = 123;

        $branch = new Branch('DB', 'Dummy Branch', 'Description');
        $ticket = new Ticket(
            new UniqueId($uniqueId),
            new TicketSequenceNumber($ticketSequenceNumber),
            'Subject',
            'Description',
            $branch,
            new User(1, User::TYPE_DIAMANTE),
            new OroUser(),
            new Source(Source::WEB),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::NEW_ONE)
        );

        $this->em
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('select')
            ->with($this->equalTo('t'))
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('from')
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('where')
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->will($this->returnValue($this->queryBuilder));

        $this->queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($this->searchQuery));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('newHydrator')
            ->with($this->equalTo(Query::HYDRATE_OBJECT))
            ->will($this->returnValue($this->objectHydrator));

        $this->objectHydrator
            ->expects($this->atLeastOnce())
            ->method('hydrateAll')
            ->will($this->returnValue($ticket));

        $result = $this->repository->get($id);

        $this->assertNotNull($result);
        $this->assertEquals($ticket, $result);
    }

        /**
         * @test
         */
        public function testSearch()
        {
            $a = new EntityStub();
            $b = new EntityStub();
            $a->name = "DUMMY_NAME";
            $a->status = "new";
            $a->id = 1;
            $b->name = "NOT_DUMMY_NAME";
            $b->status = "closed";
            $b->id = 2;

            $entities = array($a, $b);

            $pagingConfig = new FilterPagingProperties();
            $querySearch = 'NAM';
            $conditions = array(
                array('status', 'eq', 'new')
            );

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

            $whereExpr = new \Doctrine\ORM\Query\Expr\Comparison('e.status', 'eq', 'new');

            $likeExprSubject = new \Doctrine\ORM\Query\Expr\Comparison('e.subject', 'like', '%NAM%');
            $likeExprDescription = new \Doctrine\ORM\Query\Expr\Comparison('e.description', 'like', '%NAM%');
            $whereExprOrx = new \Doctrine\ORM\Query\Expr\Orx($likeExprSubject, $likeExprDescription);

            $this->queryBuilder
                ->expects($this->atLeastOnce())
                ->method('andWhere')
                ->with(
                    $this->logicalOr(
                        $this->equalTo($whereExpr),
                        $this->equalTo($whereExprOrx)
                    )
                )
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
                ->will($this->returnValue($this->searchQuery));

            $this->queryBuilder
                ->expects($this->any())
                ->method('expr')
                ->will($this->returnValue($this->expressionBuilder));

            $this->em
                ->expects($this->any())
                ->method('getExpressionBuilder')
                ->will($this->returnValue($this->expressionBuilder));

            $this->expressionBuilder
                ->expects($this->atLeast(2))
                ->method('literal')
                ->with(
                    $this->logicalOr(
                        $this->equalTo('new'),
                        $this->equalTo('%' . $querySearch . '%')
                    )
                )
                ->will($this->returnCallback(array($this, 'literalCallback')));

            $this->expressionBuilder
                ->expects($this->atLeastOnce())
                ->method('eq')
                ->with($this->equalTo('e.status'), $this->equalTo('new'))
                ->will($this->returnValue($whereExpr));


            $this->expressionBuilder
                ->expects($this->atLeastOnce())
                ->method('Orx')
                ->with($this->anything(), $this->anything())
                ->will($this->returnValue($whereExprOrx));

            $this->em
                ->expects($this->atLeastOnce())
                ->method('newHydrator')
                ->with($this->equalTo(Query::HYDRATE_OBJECT))
                ->will($this->returnValue($this->objectHydrator));

            $this->objectHydrator
                ->expects($this->atLeastOnce())
                ->method('hydrateAll')
                ->will($this->returnValue(array($a)));

            $searchedEntities = $this->repository->search($querySearch, $conditions, $pagingConfig);

            $this->assertNotNull($searchedEntities);
            $this->assertTrue(is_array($searchedEntities));
            $this->assertNotEmpty($searchedEntities);
            $this->assertEquals($a, $searchedEntities[0]);
        }

    public function literalCallback($data)
    {
        return $data;
    }

    public function andWhereCallback($data)
    {
        return $data;
    }

    public function likeCallback($data)
    {
        return $data;
    }
} 
