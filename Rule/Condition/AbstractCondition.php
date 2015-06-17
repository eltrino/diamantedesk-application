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

namespace Diamante\AutomationBundle\Rule\Condition;

abstract class AbstractCondition implements Condition
{
    protected $expectedValue;
    protected $property;

    public function __construct($property, $value)
    {
        $this->property         = $property;
        $this->expectedValue    = $value;
    }

    protected function extractProperty($object)
    {
        $result = null;
        $method = sprintf('get%s', ucwords($this->property));

        if (property_exists($object, $this->property) && method_exists($object, $method)) {
            $result = call_user_func([$object, $method]);
        }

        return $result;
    }

    public function __toString()
    {
        return sprintf("%s[%s, %s]", $this->getClass(), $this->property, $this->expectedValue);
    }

    public function getClass()
    {
        $bits = explode("\\", __CLASS__);
        $class = lcfirst(array_pop($bits));

        return $class;
    }
}