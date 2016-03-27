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

namespace Diamante\AutomationBundle\Tests\Model;

use Diamante\AutomationBundle\Model\Condition;
use Diamante\AutomationBundle\Model\Group;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCreateCondition()
    {
        $condition = $this->createCondition();

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $condition->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\Group', $condition->getGroup());
        $this->assertEquals(Group::CONNECTOR_INCLUSIVE, $condition->getGroup()->getConnector());
        $this->assertEquals('eq', $condition->getType());
        $this->assertEquals(['status' => 'new'], $condition->getParameters());
    }

    private function createCondition()
    {
        return new Condition('eq', ['status' => 'new'], new Group(Group::CONNECTOR_INCLUSIVE));
    }
} 