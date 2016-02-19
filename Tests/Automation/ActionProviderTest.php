<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Automation;

use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;
use Diamante\AutomationBundle\Entity\WorkflowAction;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\WorkflowRule;

/**
 * Class ActionProviderTest
 *
 * @package Diamante\AutomationBundle\Automation
 */
class ActionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider
     * @Mock Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider
     */
    private $automationConfigurationProvider;


    /**
     * @var \Diamante\AutomationBundle\Automation\Action\NotifyByEmailAction
     * @Mock Diamante\AutomationBundle\Automation\Action\NotifyByEmailAction
     */
    private $notifyByEmailAction;

    /**
     * @var ActionProvider
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->container
            ->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($this->automationConfigurationProvider))
            ->with($this->equalTo('diamante_automation.config.provider'));

        $this->automationConfigurationProvider
            ->expects($this->once())
            ->method('getConfiguredActions')
            ->will($this->returnValue(new ParameterBag($this->getActions())));

        $this->service = new ActionProvider($this->container);
    }

    /**
     * @expectedException \Diamante\AutomationBundle\Exception\InvalidConfigurationException
     */
    public function testIncorrectActionName()
    {
        $rule = $this->getRule('invalid_action_name');

        $this->service->getActions($rule);
    }

    /**
     * @expectedException \Diamante\AutomationBundle\Exception\InvalidConfigurationException
     */
    public function testIncorrectService()
    {
        $rule = $this->getRule('test_action');

        $this->service->getActions($rule);
    }

    /**
     * @test
     */
    public function testGetActions()
    {
        $rule = $this->getRule();

        $this->container
            ->expects($this->once())
            ->method('has')
            ->will($this->returnValue(true));

        $this->container
            ->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue($this->notifyByEmailAction))
            ->with($this->equalTo('diamante_automation.action.update_property'));

        $actions = $this->service->getActions($rule);

        $this->assertTrue(in_array($this->notifyByEmailAction, $actions));
    }

    /**
     * @return array
     */
    private function getActions()
    {
        $actions = [
            'update_property' => [
                'id' => '@diamante_automation.action.update_property',
                'frontend_label' => 'diamante.automation.action.update_property',
                'data_types' => ['*', '!user', '!users']
            ],
            'notify_by_email' => [
                'id' => '@diamante_automation.action.notify_by_email',
                'frontend_label' => 'diamante.automation.action.notify_by_email',
                'data_types' => ['*']
            ],
            'test_action' => [
                'id' => 'diamante_automation.action.test_action',
                'frontend_label' => 'diamante.automation.action.test_action',
                'data_types' => ['*']
            ],
        ];

        return $actions;
    }

    /**
     * @param string $actionName
     *
     * @return WorkflowRule
     */
    private function getRule($actionName = 'update_property')
    {
        $rule = new WorkflowRule('workflow_rule', 'ticket');
        $group = new Group(Group::CONNECTOR_INCLUSIVE);
        $equalCondition = new Condition('Eq', ['status' => 'new'], $group);
        $notEqualCondition = new Condition('Neq', ['status' => 'open'], $group);
        $action = new WorkflowAction($actionName, ['status' => 'closed'], $rule);

        $rule->setGrouping($group);
        $rule->addAction($action);
        $group->addCondition($equalCondition);
        $group->addCondition($notEqualCondition);

        return $rule;
    }
}