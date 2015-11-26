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

namespace Diamante\AutomationBundle\Rule\Condition;


use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;

class ConditionFactory
{
    const CONDITION_PARSE_FORMAT = '/^([a-zA-Z]+)\[([a-zA-Z]+)\,\s+(.*\S)\]/';

    /**
     * @var ParameterBag
     */
    protected $conditions;

    /**
     * ConditionFactory constructor.
     * @param AutomationConfigurationProvider $configurationProvider
     */
    public function __construct(AutomationConfigurationProvider $configurationProvider)
    {
        $this->conditions = $configurationProvider->getConfiguredConditions();
    }

    /**
     * @param $type
     * @param array $parameters
     * @return ConditionInterface
     */
    public function getCondition($type, array $parameters)
    {
        if (!$this->conditions->has($type)) {
            throw new \RuntimeException(sprintf("Unknown condition type %s", $type));
        }

        $class = $this->conditions->get(sprintf("%s.class", $type));

        if (!class_exists($class)) {
            throw new \RuntimeException(
                sprintf(
                    "Invalid class configuration for condition '%s'. Class '%s' does not exist",
                    $type,
                    $class
                    )
            );
        }

        $property = key($parameters);
        $expectedValue = $parameters[$property];

        return new $class($property, $expectedValue);
    }
}