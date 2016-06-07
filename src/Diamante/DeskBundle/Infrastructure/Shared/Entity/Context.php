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

/**
 * Class Context
 *
 * @package Diamante\AutomationBundle\Rule\Condition
 */
class Context
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
     * @var array|null
     */
    protected $propertyAccessor;

    /**
     * @var string
     */
    protected $mode;

    /**
     * Context constructor.
     *
     * @param            $property
     * @param            $expectedValue
     * @param array|null $propertyAccessor
     */
    public function __construct($property, $expectedValue, array $propertyAccessor = null)
    {
        $this->property = $property;
        $this->expectedValue = $expectedValue;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getExpectedValue()
    {
        return $this->expectedValue;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function setExpectedValue($value)
    {
        return $this->expectedValue = $value;
    }

    /**
     * @return array|null
     */
    public function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
}