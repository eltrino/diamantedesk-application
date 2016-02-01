<?php
/*
 * Copyright (c) 2016 Eltrino LLC (http://eltrino.com)
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


abstract class AbstractAccessorAwareCondition extends AbstractCondition
{
    /**
     * @var object
     */
    protected $accessor;

    /**
     * @var string
     */
    protected $accessorMethod;

    /**
     * AbstractAccessorAwareCondition constructor.
     * @param $property
     * @param $expectedValue
     * @param $accessor
     * @param $accessorMethod
     */
    public function __construct($property, $expectedValue, $accessor, $accessorMethod)
    {
        parent::__construct($property, $expectedValue);

        $this->accessor         = $accessor;
        $this->accessorMethod   = $accessorMethod;
    }

    /**
     * @param $object
     * @return mixed
     */
    protected function extractPropertyValue($object)
    {
        return call_user_func([$this->accessor, $this->accessorMethod], $object);
    }
}