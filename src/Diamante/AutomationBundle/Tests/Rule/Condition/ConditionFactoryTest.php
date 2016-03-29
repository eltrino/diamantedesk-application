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

namespace Diamante\AutomationBundle\Tests\Rule\Condition;

use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;

/**
 * Class ConditionFactoryTest
 *
 * @package Diamante\AutomationBundle\Tests\Rule\Condition
 */
class ConditionFactoryTest extends \PHPUnit_Framework_TestCase
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
    private $configurationProvider;

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo('diamante_automation.config.provider'))
            ->will($this->returnValue($this->configurationProvider));

        $this->conditionFactory = new ConditionFactory($this->container);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testIncorrectType()
    {
        $this->configurationProvider
            ->expects($this->any())
            ->method('getConfiguredConditions')
            ->will($this->returnValue(new ParameterBag($this->getConditions())));

        $this->conditionFactory->getCondition('incorrect_type', ['status' => 'open'], 'ticket');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNonExistingClass()
    {
        $this->configurationProvider
            ->expects($this->any())
            ->method('getConfiguredConditions')
            ->will($this->returnValue(new ParameterBag($this->getConditions())));

        $this->conditionFactory->getCondition('test_condition', ['status' => 'open'], 'ticket');
    }

    /**
     * @test
     */
    public function testGetCondition()
    {
        $this->configurationProvider
            ->expects($this->any())
            ->method('getConfiguredConditions')
            ->will($this->returnValue(new ParameterBag($this->getConditions())));

        $condition = $this->conditionFactory->getCondition('eq', ['status' => 'open'], 'ticket');

        $this->assertEquals('eq', $condition->getName());
        $this->assertInstanceOf('Diamante\AutomationBundle\Rule\Condition\Expression\Eq', $condition);
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
} 