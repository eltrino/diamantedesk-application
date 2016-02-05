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


use Diamante\DeskBundle\Model\Shared\Property;

abstract class AbstractCondition implements ConditionInterface
{
    /**
     * @var string
     */
    protected $property;
    /**
     * @var mixed
     */
    protected $expectedValue;
    /**
     * @var string
     */
    protected $name;

    /**
     * AbstractCondition constructor.
     * @param $property
     * @param $expectedValue
     */
    public function __construct($property, $expectedValue)
    {
        $this->property         = $property;
        $this->expectedValue    = $expectedValue;

        $this->name             = $this->getClassName();
    }

    /**
     * @param $object
     * @return mixed|null
     */
    protected function extractPropertyValue($object)
    {
        $result = null;
        $method = sprintf("get%s", ucwords($this->property));

        if (method_exists($object, $method) && property_exists($object, $this->property)) {
            $result = call_user_func([$object, $method]);
        }

        if (is_object($result)) {
            if ($result instanceof Property) {
                $result = $result->getValue();
            } elseif (method_exists($result, '__toString')) {
                $result = (string)$result;
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function export()
    {
        return [$this->property, $this->name, $this->expectedValue];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s[%s,%s]', strtolower($this->name), $this->property, $this->expectedValue);
    }

    /**
     * @return string
     */
    protected function getClassName()
    {
        $classReflection = new \ReflectionClass($this);
        return lcfirst($classReflection->getShortName());
    }
}