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

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCreateGroup()
    {
        $group = $this->createGroup();

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $group->getId());
        $this->assertEquals(Group::CONNECTOR_INCLUSIVE, $group->getConnector());
        $this->assertInstanceOf('\DateTime', $group->getUpdatedAt());
        $this->assertInstanceOf('\DateTime', $group->getCreatedAt());
    }

    /**
     * @test
     */
    public function testCreateGroupWithParent()
    {
        $group = $this->createGroup();
        $parent = $this->createGroup();
        $group->setParent($parent);

        $this->assertEquals($parent, $group->getParent());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\Group', $group->getParent());

    }

    /**
     * @test
     */
    public function testCreateGroupWithChild()
    {
        $group = $this->createGroup();
        $child = $this->createGroup();
        $group->addChild($child);

        $this->assertEquals(true, $group->getChildren()->contains($child));
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\Group', $group->getChildren()->first());

    }

    /**
     * @test
     */
    public function testCreateGroupWithCondition()
    {
        $group = $this->createGroup();
        $condition = new Condition('eq', ['status' => 'new'], $group);
        $group->addCondition($condition);

        $this->assertEquals(true, $group->getConditions()->contains($condition));
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\Condition', $group->getConditions()->first());
    }

    private function createGroup()
    {
        return new Group(Group::CONNECTOR_INCLUSIVE);
    }
} 