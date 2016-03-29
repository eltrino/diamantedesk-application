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

use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Model\Action;
use Diamante\AutomationBundle\Model\BusinessRule;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCreateActionWithWorkflowRule()
    {
        $action = new Action(
            'NotifyByEmail',
            ['mike@diamantedesk.com'],
            new WorkflowRule('workflow_rule_name', 'ticket')
        );

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $action->getId());
        $this->assertEquals('NotifyByEmail', $action->getType());
        $email = array_pop($action->getParameters());
        $this->assertEquals('mike@diamantedesk.com', $email);
        $this->assertEquals('workflow_rule_name', $action->getRule()->getName());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $action->getRule());
        $this->assertEquals(0, $action->getWeight());
    }

    /**
     * @test
     */
    public function testCreateActionWithBusinessRule()
    {
        $action = new Action(
            'NotifyByEmail',
            ['mike@diamantedesk.com'],
            new BusinessRule('business_rule_name', 'ticket', '5m')
        );

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $action->getId());
        $this->assertEquals('NotifyByEmail', $action->getType());
        $email = array_pop($action->getParameters());
        $this->assertEquals('mike@diamantedesk.com', $email);
        $this->assertEquals('business_rule_name', $action->getRule()->getName());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $action->getRule());
        $this->assertEquals(0, $action->getWeight());
    }
} 