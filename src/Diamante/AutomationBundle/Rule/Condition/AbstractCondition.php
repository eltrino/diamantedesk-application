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
use Diamante\DeskBundle\Infrastructure\Shared\Entity\Context;
use Diamante\DeskBundle\Infrastructure\Shared\Entity\PropertyProcessingManager;

abstract class AbstractCondition implements ConditionInterface
{
    const STRICT = 'strict';
    const SOFT = 'soft';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var PropertyProcessingManager
     */
    protected $propertyManager;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @param AbstractFact $fact
     *
     * @return mixed
     */
    public function getActualValue(AbstractFact $fact)
    {
        $type = $fact->getTargetType();
        $propertyHandler = $this->propertyManager->getPropertyHandler($type);
        $this->context->setMode(static::MODE);
        $propertyHandler->setContext($this->context);
        $value = $propertyHandler->extractPropertyValue($fact);

        return $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getClassName();
    }

    /**
     * @return array
     */
    public function export()
    {
        return [$this->context->getProperty(), $this->getName(), $this->context->getExpectedValue()];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            '%s[%s,%s]',
            strtolower($this->getName()),
            $this->context->getProperty(),
            $this->context->getExpectedValue()
        );
    }

    /**
     * @return string
     */
    protected function getClassName()
    {
        $classReflection = new \ReflectionClass($this);

        return lcfirst($classReflection->getShortName());
    }

    /**
     * @param PropertyProcessingManager $propertyManager
     */
    public function setPropertyManager(PropertyProcessingManager $propertyManager)
    {
        $this->propertyManager = $propertyManager;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}