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

namespace Diamante\DeskBundle\Infrastructure\Shared\Entity;

use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Rule\Fact\AbstractFact;
use Diamante\DeskBundle\Infrastructure\Shared\StringUtils;
use Diamante\DeskBundle\Model\Shared\Property;
use Diamante\DeskBundle\Model\Shared\Weightable;
use Diamante\UserBundle\Model\User;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

abstract class AbstractPropertyHandler implements PropertyHandler
{
    use StringUtils;

    const STRICT = 'strict';
    const SOFT = 'soft';

    /**
     * @var AutomationConfigurationProvider
     */
    protected $configProvider;

    /**
     * @var string
     */
    protected $property;
    /**
     * @var mixed
     */
    protected $expectedValue;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var array|null
     */
    protected $propertyAccessor;

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
        $this->property = $context->getProperty();
        $this->expectedValue = $context->getExpectedValue();
        $this->propertyAccessor = $context->getPropertyAccessor();
    }

    /**
     * @param AbstractFact $fact
     *
     * @return mixed|null|string
     */
    public function extractPropertyValue(AbstractFact $fact)
    {
        $target = $fact->getTarget();
        $targetType = $fact->getTargetType();
        $value = $this->extract($target, $targetType);

        return $value;
    }

    /**
     * @param string $targetType
     * @param        $propertyValue
     *
     * @return mixed
     */
    public function processPropertyValue($targetType, $propertyValue)
    {
        $target = [$this->property => $propertyValue];
        $value = $this->extract($target, $targetType);

        return $value;
    }

    /**
     * @param array  $target
     * @param string $targetType
     *
     * @return mixed
     */
    protected function extract(array $target, $targetType)
    {
        $result = null;
        $propertyType = $this->configProvider->getType($targetType, $this->property);
        $propertyGetter = sprintf('get%s', $this->camelize($this->property));
        $typeGetter = sprintf('getBy%sType', $this->camelize($propertyType));

        if (array_key_exists($this->property, $target) || AutomationConfigurationProvider::VIRTUAL == $propertyType) {
            if (method_exists($this, $propertyGetter)) {
                $result = $this->$propertyGetter($target);
            } elseif (method_exists($this, $typeGetter)) {
                $result = $this->$typeGetter($target);
            } else {
                throw new \RuntimeException('Invalid email constant.');
            }
        }

        return $result;
    }

    /**
     * @param array $target
     *
     * @return mixed
     */
    protected function getByVirtualType(array $target)
    {
        list($accessor, $accessorMethod) = $this->propertyAccessor;

        return call_user_func([$accessor, $accessorMethod], $target);
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getByStringType(array $target)
    {
        return $this->getStringValue($target);
    }

    /**
     * @param array $target
     *
     * @return mixed
     */
    protected function getByDatetimeType(array $target)
    {
        return $target[$this->property];
    }

    /**
     * @param array $target
     *
     * @return bool
     */
    protected function getByBoolType(array $target)
    {
        return $target[$this->property];
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getByUserType(array $target)
    {
        $property = $target[$this->property];

        if ($property instanceof OroUser) {
            $property = User::fromEntity($property);
        }

        return (string)$property;
    }

    /**
     * @param array $target
     *
     * @return integer
     */
    protected function getWeightable(array $target)
    {
        /** @var Weightable $property */
        $property = $target[$this->property];
        $expectedValue = $property->getWeight($this->expectedValue);
        $this->context->setExpectedValue($expectedValue);
        /** @var Property $property */
        $value = $property->getValue();

        /** @var Weightable $property */
        return $property->getWeight($value);
    }

    /**
     * @param array $target
     *
     * @return mixed
     */
    protected function getPropertyInstanceValue(array $target)
    {
        /** @var Property $property */
        $property = $target[$this->property];

        return $property->getValue();
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getStringValue(array $target)
    {
        $property = $target[$this->property];

        if (method_exists($property, '__toString')) {
            $property = (string)$property;
        }

        return $property;
    }

    /**
     * @param AutomationConfigurationProvider $configProvider
     */
    public function setConfigProvider(AutomationConfigurationProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }
}
