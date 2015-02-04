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

namespace Diamante\DeskBundle\Tests\Model\Ticket\Filter;

use Diamante\DeskBundle\Api\Command\Filter\FilterCommentsCommand;
use Diamante\DeskBundle\Model\Ticket\Filter\CommentFilterCriteriaProcessor;

class CommentFilterCriteriaProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterCommentsCommand
     */
    private $commentFilterCommand;

    public function setUp()
    {
        $this->commentFilterCommand = new FilterCommentsCommand();
        $this->commentFilterCommand->ticket = 1;
        $this->commentFilterCommand->author = 'diamante_1';
        $this->commentFilterCommand->content  = 'Test';
    }

    /**
     * @test
     */
    public function testGetCriteria()
    {
        $processor = new CommentFilterCriteriaProcessor();
        $processor->setCommand($this->commentFilterCommand);
        $expectedCriteria = array(
            array('author', 'eq', 'diamante_1'),
            array('content', 'like', 'Test'),
            array('ticket', 'eq', 1),
        );

        $criteria = $processor->getCriteria();

        $this->assertNotEmpty($criteria);
        $this->assertCount(3, $criteria);
        for ($i = 0; $i<count($expectedCriteria); $i++) {
            $this->assertEquals($expectedCriteria[$i], $criteria[$i]);
        }
    }

    /**
     * @test
     */
    public function testGetPagingPropertiesWithDefaultValues()
    {
        $processor = new CommentFilterCriteriaProcessor();
        $processor->setCommand($this->commentFilterCommand);
        $pagingProperties = $processor->getPagingProperties();

        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Shared\Filter\PagingProperties', $pagingProperties);
        $this->assertEquals(25, $pagingProperties->getLimit());
        $this->assertEquals(1, $pagingProperties->getPage());
        $this->assertEquals('id', $pagingProperties->getSort());
        $this->assertEquals('ASC', $pagingProperties->getOrder());
    }

    /**
     * @test
     */
    public function testGetPagingPropertiesWithModifiedValues()
    {
        $command = new FilterCommentsCommand();
        $command->limit = 50;
        $command->page = 2;
        $command->sort = 'subject';
        $command->order = 'DESC';

        $processor = new CommentFilterCriteriaProcessor();
        $processor->setCommand($command);
        $pagingProperties = $processor->getPagingProperties();

        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Shared\Filter\PagingProperties', $pagingProperties);
        $this->assertEquals(50, $pagingProperties->getLimit());
        $this->assertEquals(2, $pagingProperties->getPage());
        $this->assertEquals('subject', $pagingProperties->getSort());
        $this->assertEquals('DESC', $pagingProperties->getOrder());
    }
}
