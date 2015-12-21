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
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\AutomationBundle\Entity\WorkflowAction;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Entity\BusinessRule;

/**
 * Class RuleServiceImplTest
 *
 * @package Diamante\AutomationBundle\Tests\Api\Internal
 */
class RuleServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const LOAD = 'load';
    const DELETE = 'delete';
    const INCORRECT_ACTION = 'move';

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     * @Mock Symfony\Bridge\Doctrine\RegistryInterface
     */
    private $registry;


    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock Doctrine\ORM\EntityManager
     */
    private $entityManager;

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
     * @var RuleServiceImpl
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->service = new RuleServiceImpl(
            $this->registry,
            $this->workflowRuleRepository,
            $this->businessRuleRepository
        );
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Incorrect rule mode.
     */
    public function testIncorrectMode()
    {
        $data = ['mode' => 'incorrect_mode'];

        $this->service->actionRule($data, self::CREATE);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Rule action does not exists.
     */
    public function testIncorrectAction()
    {
        $data = ['mode' => 'workflow'];

        $this->service->actionRule($data, self::INCORRECT_ACTION);
    }

    /**
     * @test
     */
    public function testCreateWorkflowRule()
    {
        $data = [
            'name'       => 'workflow_rule',
            'target'     => 'ticket',
            'conditions' => [
                'connector'  => 'AND',
                'conditions' => [
                    [
                        'type'       => 'Eq',
                        'parameters' => ['status' => 'new']
                    ]
                ]
            ],
            'actions'    => [
                [
                    'type'       => 'UpdateProperty',
                    'parameters' => ['status' => 'closed']
                ]
            ],
            'mode'       => 'workflow'
        ];

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $rule = $this->service->actionRule($data, self::CREATE);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $rule);
    }

    /**
     * @test
     */
    public function testCreateBusinessRule()
    {
        $data = [
            'name'         => 'business_rule',
            'target'       => 'ticket',
            'timeInterval' => '4h',
            'conditions'   => [
                'connector'  => 'AND',
                'conditions' => [
                    [
                        'type'       => 'Eq',
                        'parameters' => ['status' => 'new']
                    ]
                ]
            ],
            'actions'      => [
                [
                    'type'       => 'UpdateProperty',
                    'parameters' => ['status' => 'closed']
                ]
            ],
            'mode'         => 'business'
        ];

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

        $rule = $this->service->actionRule($data, self::CREATE);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $rule);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Rule loading failed. Rule not found.
     */
    public function testLoadBusinessRuleException()
    {
        $data = [
            'id'   => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'mode' => 'business'
        ];

        $this->service->actionRule($data, self::LOAD);
    }


    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Rule loading failed. Rule not found.
     */
    public function testLoadWorkflowRuleException()
    {
        $data = [
            'id'   => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'mode' => 'workflow'
        ];

        $this->service->actionRule($data, self::LOAD);
    }

    /**
     * @test
     */
    public function testLoadBusinessRule()
    {
        $rule = $this->getBusinessRule();
        $data = [
            'id'   => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'mode' => 'business'
        ];

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($data['id'])
            ->will($this->returnValue($rule));

        $result = $this->service->actionRule($data, self::LOAD);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $result);
    }

    /**
     * @test
     */
    public function testLoadWorkflowRule()
    {
        $rule = $this->getWorkflowRule();
        $data = [
            'id'   => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'mode' => 'workflow'
        ];

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($data['id'])
            ->will($this->returnValue($rule));

        $result = $this->service->actionRule($data, self::LOAD);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $rule->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $result);
    }

    /**
     * @test
     */
    public function testDeleteWorkflowRule()
    {
        $rule = $this->getWorkflowRule();
        $data = [
            'id'   => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'mode' => 'workflow'
        ];

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($data['id'])
            ->will($this->returnValue($rule));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $this->service->actionRule($data, self::DELETE);
    }

    /**
     * @test
     */
    public function testDeleteBusinessRule()
    {
        $rule = $this->getBusinessRule();
        $data = [
            'id'   => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'mode' => 'business'
        ];

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($data['id'])
            ->will($this->returnValue($rule));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $this->service->actionRule($data, self::DELETE);
    }

    /**
     * @test
     */
    public function testUpdateBusinessRule()
    {
        $rule = $this->getBusinessRule();
        $data = [
            'id'           => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'name'         => 'business_rule',
            'target'       => 'ticket',
            'timeInterval' => '4h',
            'conditions'   => [
                'connector'  => 'AND',
                'conditions' => [
                    [
                        'type'       => 'Eq',
                        'parameters' => ['status' => 'new']
                    ],
                    [
                        'type'       => 'Neq',
                        'parameters' => ['status' => 'open']
                    ]
                ]
            ],
            'actions'      => [
                [
                    'type'       => 'NotifyByEmail',
                    'parameters' => ['mike@diamantedesk.com']
                ]
            ],
            'active'       => true,
            'mode'         => 'business'
        ];

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($data['id'])
            ->will($this->returnValue($rule));

        $this->businessRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\BusinessRule'));

        $result = $this->service->actionRule($data, self::UPDATE);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $result->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\BusinessRule', $result);
        $this->assertEquals(2, $result->getRootGroup()->getConditions()->count());
        $this->assertEquals('NotifyByEmail', $result->getActions()->first()->getType());
    }

    /**
     * @test
     */
    public function testUpdateWorkflowRule()
    {
        $rule = $this->getWorkflowRule();
        $data = [
            'id'         => '2a9475e1-c81f-461e-a450-46790a77abfc',
            'name'       => 'workflow_rule',
            'target'     => 'ticket',
            'conditions' => [
                'connector'  => 'AND',
                'conditions' => [
                    [
                        'type'       => 'Eq',
                        'parameters' => ['status' => 'new']
                    ],
                    [
                        'type'       => 'Neq',
                        'parameters' => ['status' => 'open']
                    ]
                ]
            ],
            'actions'    => [
                [
                    'type'       => 'NotifyByEmail',
                    'parameters' => ['mike@diamantedesk.com']
                ]
            ],
            'active'     => true,
            'mode'       => 'workflow'
        ];

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('get')
            ->with($data['id'])
            ->will($this->returnValue($rule));

        $this->workflowRuleRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->isInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule'));

        $result = $this->service->actionRule($data, self::UPDATE);

        $this->assertInstanceOf('Rhumsaa\Uuid\Uuid', $result->getId());
        $this->assertInstanceOf('Diamante\AutomationBundle\Model\WorkflowRule', $result);
        $this->assertEquals(2, $result->getRootGroup()->getConditions()->count());
        $this->assertEquals('NotifyByEmail', $result->getActions()->first()->getType());
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

        $rule->setRootGroup($group);
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
        $action = new WorkflowAction('UpdateProperty', ['status' => 'closed'], $rule);

        $rule->setRootGroup($group);
        $rule->addAction($action);
        $group->addCondition($equalCondition);
        $group->addCondition($notEqualCondition);

        return $rule;
    }
} 