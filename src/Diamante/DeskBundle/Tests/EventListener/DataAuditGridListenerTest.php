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
namespace Diamante\DeskBundle\Tests\EventListener;

use Diamante\DeskBundle\EventListener\DataAuditGridListener;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;

/**
 * Class DataAuditGridListenerTest
 *
 * @package Diamante\DeskBundle\Tests\EventListener
 */
class DataAuditGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataAuditGridListener
     */
    private $dataAuditGridListener;

    /**
     * @var \Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface
     * @Mock Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface
     */
    private $datagrid;

    /**
     * @var \Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration
     * @Mock Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration
     */
    private $config;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->dataAuditGridListener = new DataAuditGridListener();
    }

    /**
     * @test
     */
    public function testOnBuildBefore()
    {
        $event = new BuildBefore($this->datagrid, $this->config);
        $this->dataAuditGridListener->onBuildBefore($event);
    }
}
