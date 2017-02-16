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
use Diamante\AutomationBundle\Entity\TimeTriggeredAction;
use Diamante\AutomationBundle\Entity\TimeTriggeredGroup;
use Diamante\AutomationBundle\Entity\TimeTriggeredRule;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\EventTriggeredAction;
use Diamante\AutomationBundle\Entity\EventTriggeredGroup;
use Diamante\AutomationBundle\Entity\EventTriggeredRule;
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
    private $eventTriggeredRuleRepository;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $timeTriggeredRuleRepository;

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
            $this->eventTriggeredRuleRepository,
            $this->timeTriggeredRuleRepository,
            $this->validator
        );
    }

    /**
     * @test
     */
    public function testCreateEventTriggeredRule()
    {
        $input = $this->getJsonRule(Rule::TYPE_EVENT_TRIGGERED);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule'));

        $rule = $this->ruleService->createRule($input);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule', $rule);
    }

    /**
     * @test
     */
    public function testCreateTimeTriggeredRule()
    {
        $input = $this->getJsonRule(Rule::TYPE_TIME_TRIGGERED);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule'));

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

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule', $rule);
    }

    /**
     * @test
     */
    public function testUpdateEventTriggeredRule()
    {
        $eventTriggeredRule = $this->getEventTriggeredRule();
        $input = $this->getJsonRule(Rule::TYPE_EVENT_TRIGGERED);
        $ruleId = 'rule_id';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($eventTriggeredRule));

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule'));

        $rule = $this->ruleService->updateRule($input, $ruleId);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule', $rule);
        $this->assertEquals(2, $rule->getGrouping()->getConditions()->count());
        $this->assertEquals('update_property', $rule->getActions()->first()->getType());
    }

    /**
     * @test
     */
    public function testUpdateTimeTriggeredRule()
    {
        $timeTriggeredRule = $this->getTimeTriggeredRule();
        $input = $this->getJsonRule(Rule::TYPE_TIME_TRIGGERED);
        $ruleId = 'rule_id';

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($timeTriggeredRule));

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule'));

        $rule = $this->ruleService->updateRule($input, $ruleId);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule', $rule);
        $this->assertEquals(2, $rule->getGrouping()->getConditions()->count());
        $this->assertEquals('update_property', $rule->getActions()->first()->getType());
    }

    /**
     * @test
     */
    public function testDeleteEventTriggeredRule()
    {
        $ruleId = 'rule_id';
        $eventTriggeredRule = $this->getEventTriggeredRule();

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($eventTriggeredRule));

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule'));

        $this->ruleService->deleteRule(Rule::TYPE_EVENT_TRIGGERED, $ruleId);
    }

    /**
     * @test
     */
    public function testDeleteTimeTriggeredRule()
    {
        $ruleId = 'rule_id';
        $timeTriggeredRule = $this->getTimeTriggeredRule();

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($timeTriggeredRule));

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule'));

        $this->ruleService->deleteRule(Rule::TYPE_TIME_TRIGGERED, $ruleId);
    }

    /**
     * @test
     */
    public function testActivateTimeTriggeredRule()
    {
        $ruleId = 'rule_id';
        $timeTriggeredRule = $this->getTimeTriggeredRule();

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($timeTriggeredRule));

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule'));

        $rule = $this->ruleService->activateRule(Rule::TYPE_TIME_TRIGGERED, $ruleId);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule', $rule);
        $this->assertTrue($rule->isActive());
    }

    /**
     * @test
     */
    public function testActivateEventTriggeredRule()
    {
        $ruleId = 'rule_id';
        $eventTriggeredRule = $this->getEventTriggeredRule();

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($eventTriggeredRule));

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule'));

        $rule = $this->ruleService->activateRule(Rule::TYPE_EVENT_TRIGGERED, $ruleId);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule', $rule);
        $this->assertTrue($rule->isActive());
    }

    /**
     * @test
     */
    public function testDeactivateEventTriggeredRule()
    {
        $ruleId = 'rule_id';
        $eventTriggeredRule = $this->getEventTriggeredRule();

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($eventTriggeredRule));

        $this->eventTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule'));

        $rule = $this->ruleService->deactivateRule(Rule::TYPE_EVENT_TRIGGERED, $ruleId);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\EventTriggeredRule', $rule);
        $this->assertFalse($rule->isActive());
    }

    /**
     * @test
     */
    public function testDeactivateTimeTriggeredRule()
    {
        $ruleId = 'rule_id';
        $timeTriggeredRule = $this->getTimeTriggeredRule();

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($ruleId)
            ->will($this->returnValue($timeTriggeredRule));

        $this->timeTriggeredRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule'));

        $rule = $this->ruleService->deactivateRule(Rule::TYPE_TIME_TRIGGERED, $ruleId);

        $this->assertInstanceOf('Ramsey\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\TimeTriggeredRule', $rule);
        $this->assertFalse($rule->isActive());
    }

    /**
     * @return EventTriggeredRule
     */
    private function getEventTriggeredRule()
    {
        $rule = new EventTriggeredRule('event_triggered_rule', 'ticket');
        $group = new EventTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $equalCondition = new Condition('Eq', ['status' => 'new'], $group);
        $notEqualCondition = new Condition('Neq', ['status' => 'open'], $group);
        $action = new EventTriggeredAction('UpdateProperty', ['status' => 'closed'], $rule);

        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($equalCondition);
        $group->addCondition($notEqualCondition);

        return $rule;
    }

    /**
     * @return TimeTriggeredRule
     */
    private function getTimeTriggeredRule()
    {
        $rule = new TimeTriggeredRule('time_triggered_rule', 'ticket', '5m');
        $group = new TimeTriggeredGroup(Group::CONNECTOR_INCLUSIVE);
        $equalCondition = new Condition('Eq', ['status' => 'new'], $group);
        $notEqualCondition = new Condition('Neq', ['status' => 'open'], $group);
        $action = new TimeTriggeredAction('UpdateProperty', ['status' => 'closed'], $rule);

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

        if (Rule::TYPE_TIME_TRIGGERED == $type) {
            $rule['timeInterval'] = '5m';
        }

        return json_encode($rule);
    }
} 
