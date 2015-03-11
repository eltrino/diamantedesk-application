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
namespace Diamante\ApiBundle\Routine\Tests;

use Rhumsaa\Uuid\Console\Exception;

class ResponseAnalyzer
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @param string $method
     * @return $this
     */
    public function expects($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function will($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    public function analyze()
    {
        $responseValue = call_user_func(array($this->response, $this->method));
        if ($responseValue != $this->value) {
            $message = sprintf('Expected status code %s, got %s', $this->value, $responseValue);
            throw new Exception($message);
        }
    }
}
