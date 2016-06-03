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


use Diamante\AutomationBundle\Rule\Fact\AbstractFact;
use Diamante\DeskBundle\Model\Shared\Property;
use Diamante\DeskBundle\Model\Shared\Weightable;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\UserBundle\Model\User;

abstract class AbstractCondition implements ConditionInterface
{
    const STRICT = 'strict';
    const SOFT = 'soft';

    /**
     * @var string
     */
    protected $property;
    /**
     * @var mixed
     */
    protected $expectedValue;

    protected $propertyAccessor;
    /**
     * @var string
     */
    protected $name;

    /**
     * AbstractCondition constructor.
     *
     * @param            $property
     * @param            $expectedValue
     * @param array|null $propertyAccessor
     */
    public function __construct($property, $expectedValue, array $propertyAccessor = null)
    {
        $this->property         = $property;
        $this->expectedValue    = $expectedValue;
        $this->propertyAccessor = $propertyAccessor;

        $this->name             = $this->getClassName();
    }

    /**
     * @param AbstractFact $fact
     *
     * @return null|string
     */
    protected function extractPropertyValue(AbstractFact $fact)
    {
        $target = $fact->getTarget();

        if ($this->isVirtualProperty()) {
            return $this->extractVirtualProperty($target);
        }

        return $this->extractRealProperty($target);
    }

    /**
     * @param array $target
     *
     * @return mixed
     */
    protected function extractVirtualProperty(array $target)
    {
        list($accessor, $accessorMethod) = $this->propertyAccessor;

        return call_user_func([$accessor, $accessorMethod], $target);
    }

    /**
     * @param array $target
     *
     * @return null|string
     */
    protected function extractRealProperty(array $target)
    {
        $result = null;

        if (array_key_exists($this->property, $target)) {
            $result = $target[$this->property];

            if ($result instanceof OroUser) {
                $result = User::fromEntity($result);
            }
        }

        $result = $this->typeJuggling($result);

        return $result;
    }

    protected function isVirtualProperty()
    {
        if (!is_null($this->propertyAccessor)) {
            return true;
        }

        return false;
    }

    protected function typeJuggling($property) {
        if (is_object($property)) {
            if ($property instanceof Weightable) {
                $this->expectedValue = $property->getWeight($this->expectedValue);
                $property = $property->getWeight($property->getValue());
            } elseif (static::MODE == self::STRICT && $property instanceof Property) {
                $property = $property->getValue();
            } elseif (method_exists($property, '__toString')) {
                $property = (string)$property;
            }
        }

        return $property;
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