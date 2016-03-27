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

namespace Diamante\AutomationBundle\Tests\Configuration;

use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;

class AutomationConfigurationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var AutomationConfigurationProvider
     */
    private $service;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->container
            ->expects($this->any())
            ->method('hasParameter')
            ->will($this->returnValue(true))
            ->with($this->isType('string'));

        $this->container
            ->expects($this->any())
            ->method('getParameter')
            ->will(
                $this->returnCallback(
                    function ($paramName) {
                        switch ($paramName) {
                            case 'diamante.automation.config.actions':
                                return $this->getActions();
                                break;
                            case 'diamante.automation.config.conditions':
                                return $this->getConditions();
                                break;
                            default:
                                return [];
                        }
                    }
                )
            )
            ->with($this->isType('string'));

        $this->service = new AutomationConfigurationProvider($this->container);
    }

    /**
     * @test
     */
    public function testGetConfiguredActions()
    {
        $actions = $this->service->getConfiguredActions();

        $this->assertInstanceOf('Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag', $actions);
    }

    /**
     * @test
     */
    public function testGetConfiguredConditions()
    {
        $conditions = $this->service->getConfiguredConditions();

        $this->assertInstanceOf('Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag', $conditions);
    }

    /**
     * @return array
     */
    private function getConditions()
    {
        $conditions = [
            'eq' => [
                'class' => 'Diamante\AutomationBundle\Rule\Condition\Expression\Eq',
                'frontend_label' => 'diamante.automation.condition.eq'
            ],
            'neq' => [
                'class' => 'Diamante\AutomationBundle\Rule\Condition\Expression\Neq',
                'frontend_label' => 'diamante.automation.condition.neq'
            ],
            'test_condition' => [
                'class' => 'Diamante\AutomationBundle\Rule\Condition\Expression\TestClass',
                'frontend_label' => 'diamante.automation.condition.test_class'
            ]
        ];

        return $conditions;
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
}