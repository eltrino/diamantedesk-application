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

namespace Diamante\AutomationBundle\Rule\Action;


use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;
use Diamante\AutomationBundle\Rule\Fact\Fact;

class ExecutionContext
{
    const EXECUTION_SUCCESS = 0;
    const EXECUTION_FAILED  = 255;
    const EXECUTION_PENDING = 1;

    protected $fact;
    protected $parameters;
    protected $executionResult = self::EXECUTION_PENDING;
    protected $errors = [];

    public function __construct(array $parameters)
    {
        $this->parameters   = new ParameterBag($parameters);
    }

    /**
     * @param Fact $fact
     */
    public function setFact(Fact $fact)
    {
        $this->fact = $fact;
    }

    /**
     * @return mixed
     */
    public function getFact()
    {
        return $this->fact;
    }

    /**
     * @return ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return int
     */
    public function getExecutionResult()
    {
        return $this->executionResult;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param int $code
     */
    public function setExecutionResult($code)
    {
        $this->executionResult = $code;
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return (bool)count($this->errors);
    }
}