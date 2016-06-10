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

namespace Diamante\AutomationBundle\Tests\Api\Internal;

use Diamante\AutomationBundle\Api\Internal\RuleServiceImpl;
use Diamante\AutomationBundle\Entity\BusinessAction;
use Diamante\AutomationBundle\Entity\BusinessRule;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\WorkflowAction;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Model\Rule;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

/**
 * Class RuleServiceImplTest
 *
 * @package Diamante\AutomationBundle\Tests\Api\Internal
 */
class RuleServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     * @Mock Symfony\Bridge\Doctrine\RegistryInterface
     */
    private $registry;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $workflowRuleRepository;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $businessRuleRepository;

    /**
     * @var \Diamante\AutomationBundle\Automation\Validator\RuleValidator
     * @Mock Diamante\AutomationBundle\Automation\Validator\RuleValidator
     */
    private $validator;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var RuleServiceImpl
     */
    private $ruleService;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->ruleService = new RuleServiceImpl(
            $this->registry,
            $this->workflowRuleRepository,
            $this->businessRuleRepository,
            $this->validator
        );
    }

    /**
     * @test
     */
    public function testCreateWorkflowRule()
    {
        $input = $this->getJsonRule(Rule::TYPE_WORKFLOW);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $rule = $this->ruleService->createRule($input);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $rule);
    }

    /**
     * @test
     */
    public function testCreateBusinessRule()
    {
        $input = $this->getJsonRule(Rule::TYPE_BUSINESS);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $this->registry
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnValue($this->entityManager));

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('Oro\Bundle\CronBundle\Entity\Schedule'));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $rule = $this->ruleService->createRule($input);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $rule);
    }

    /**
     * @test
     */
    public function testUpdateWorkflowRule()
    {
        $workflowRule = $this->getWorkflowRule();
        $input = $this->getJsonRule(Rule::TYPE_WORKFLOW);
        $ruleId = 'rule_id';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($workflowRule));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $rule = $this->ruleService->updateRule($input, $ruleId);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $rule);
        $this->assertEquals(2, $rule->getGrouping()->getConditions()->count());
        $this->assertEquals('update_property', $rule->getActions()->first()->getType());
    }

    /**
     * @test
     */
    public function testUpdateBusinessRule()
    {
        $businessRule = $this->getBusinessRule();
        $input = $this->getJsonRule(Rule::TYPE_BUSINESS);
        $ruleId = 'rule_id';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($businessRule));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $rule = $this->ruleService->updateRule($input, $ruleId);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $rule);
        $this->assertEquals(2, $rule->getGrouping()->getConditions()->count());
        $this->assertEquals('update_property', $rule->getActions()->first()->getType());
    }

    /**
     * @test
     */
    public function testDeleteWorkflowRule()
    {
        $ruleId = 'rule_id';
        $workflowRule = $this->getWorkflowRule();

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($workflowRule));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $this->ruleService->deleteRule(Rule::TYPE_WORKFLOW, $ruleId);
    }

    /**
     * @test
     */
    public function testDeleteBusinessRule()
    {
        $ruleId = 'rule_id';
        $businessRule = $this->getBusinessRule();

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($businessRule));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $this->ruleService->deleteRule(Rule::TYPE_BUSINESS, $ruleId);
    }

    /**
     * @test
     */
    public function testActivateBusinessRule()
    {
        $ruleId = 'rule_id';
        $businessRule = $this->getBusinessRule();

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($businessRule));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $rule = $this->ruleService->activateRule(Rule::TYPE_BUSINESS, $ruleId);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $rule);
        $this->assertTrue($rule->isActive());
    }

    /**
     * @test
     */
    public function testActivateWorkflowRule()
    {
        $ruleId = 'rule_id';
        $workflowRule = $this->getWorkflowRule();

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($workflowRule));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $rule = $this->ruleService->activateRule(Rule::TYPE_WORKFLOW, $ruleId);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $rule);
        $this->assertTrue($rule->isActive());
    }

    /**
     * @test
     */
    public function testDeactivateWorkflowRule()
    {
        $ruleId = 'rule_id';
        $workflowRule = $this->getWorkflowRule();

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($workflowRule));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $rule = $this->ruleService->deactivateRule(Rule::TYPE_WORKFLOW, $ruleId);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $rule);
        $this->assertFalse($rule->isActive());
    }

    /**
     * @test
     */
    public function testDeactivateBusinessRule()
    {
        $ruleId = 'rule_id';
        $businessRule = $this->getBusinessRule();

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($businessRule));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $rule = $this->ruleService->deactivateRule(Rule::TYPE_BUSINESS, $ruleId);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $rule);
        $this->assertFalse($rule->isActive());
    }

    /**
     * @return WorkflowRule
     */
    private function getWorkflowRule()
    {
        $rule = new WorkflowRule('workflow_rule', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $equalCondition = new Condition('Eq', ['status' => 'new'], $group);
        $notEqualCondition = new Condition('Neq', ['status' => 'open'], $group);
        $action = new WorkflowAction('UpdateProperty', ['status' => 'closed'], $rule);

        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($equalCondition);
        $group->addCondition($notEqualCondition);

        return $rule;
    }

    /**
     * @return BusinessRule
     */
    private function getBusinessRule()
    {
        $rule = new BusinessRule('business_rule', 'ticket', '5m');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $equalCondition = new Condition('Eq', ['status' => 'new'], $group);
        $notEqualCondition = new Condition('Neq', ['status' => 'open'], $group);
        $action = new BusinessAction('UpdateProperty', ['status' => 'closed'], $rule);

        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($equalCondition);
        $group->addCondition($notEqualCondition);

        return $rule;
    }

    /**
     * @param $type
     *
     * @return string
     */
    private function getJsonRule($type)
    {
        $rootChildren = [
            [
                'grouping' =>
                    [
                        'connector'  => 'and',
                        'conditions' =>
                            [
                                [
                                    'type'       => 'eq',
                                    'parameters' => ['status' => 'open'],
                                ],
                            ],
                    ],
            ],
        ];

        $rootConditions = [
            [
                'type'       => 'eq',
                'parameters' =>
                    [
                        'status' => 'open',
                    ],
            ],
            [
                'type'       => 'neq',
                'parameters' =>
                    [
                        'status' => 'close',
                    ],
            ],
        ];

        $actions = [
            [
                'type'       => 'update_property',
                'parameters' =>
                    [
                        'status' => 'close',
                    ],
                'weight'     => 0,
            ],
        ];

        $rule = [
            'type'     => $type,
            'name'     => sprintf('%s rule', $type),
            'grouping' =>
                [
                    'connector'  => 'and',
                    'children'   => $rootChildren,
                    'conditions' => $rootConditions,
                ],
            'actions'  => $actions,
            'status'   => true,
            'target'   => 'ticket'
        ];

        if (Rule::TYPE_BUSINESS == $type) {
            $rule['timeInterval'] = '5m';
        }

        return json_encode($rule);
    }
} 