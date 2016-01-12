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
namespace Diamante\DeskBundle\Tests\Datagrid;

use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Datagrid\CombinedAuditDatasource;

/**
 * Class CombinedAuditDatasourceTest
 *
 * @package Diamante\DeskBundle\Tests\Datagrid
 */
class CombinedAuditDatasourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CombinedAuditDatasource
     */
    private $combinedAuditDatasource;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     * @Mock Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrineRegistry;

    /**
     * @var \Diamante\DeskBundle\Model\Audit\AuditRepository
     * @Mock Diamante\DeskBundle\Model\Audit\AuditRepository
     */
    private $auditRepository;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     * @Mock Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \Oro\Bundle\EntityBundle\ORM\QueryHintResolver
     * @Mock Oro\Bundle\EntityBundle\ORM\QueryHintResolver
     */
    private $queryHintResolver;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     * @Mock Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var \Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface
     * @Mock Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface
     */
    private $grid;

    /**
     * @var \Oro\Bundle\EntityBundle\ORM\OroEntityManager
     * @Mock Oro\Bundle\EntityBundle\ORM\OroEntityManager
     */
    private $manager;

    /**
     * @var \Oro\Bundle\DataGridBundle\Datagrid\ParameterBag
     * @Mock Oro\Bundle\DataGridBundle\Datagrid\ParameterBag
     */
    private $parameterBag;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->auditRepository
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder))
            ->with($this->equalTo('a'));

        $this->combinedAuditDatasource = new CombinedAuditDatasource(
            $this->doctrineRegistry,
            $this->auditRepository,
            $this->dispatcher,
            $this->queryHintResolver
        );
    }

    /**
     * @test
     */
    public function testProcess()
    {
        $this->doctrineRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->manager
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder))
            ->with($this->equalTo('a'));

        $this->grid
            ->expects($this->once())
            ->method('getParameters')
            ->will($this->returnValue($this->parameterBag));

        $this->parameterBag
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue(1));

        $this->grid
            ->expects($this->once())
            ->method('setDatasource');

        $this->combinedAuditDatasource->process($this->grid, []);
    }
}
