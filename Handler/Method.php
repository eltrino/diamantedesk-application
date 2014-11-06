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

namespace Diamante\ApiBundle\Handler;

use Symfony\Component\Validator\Validator;

class Method
{

    private $object;
    private $method;
    private $validator;

    public function __construct($object, $method, Validator $validator)
    {
        $this->object = $object;
        $this->method = $method;
        $this->validator = $validator;
    }

    public function invoke($arguments)
    {
        $reflectionMethod = new \ReflectionMethod($this->object, $this->method);

        $methodParameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if ($parameter->getClass()) {
                $commandClassName = $parameter->getClass()->name;

                $command = new $commandClassName;
                $commandProperties = get_class_vars($commandClassName);
                foreach ($commandProperties as $name => $value) {
                    if (array_key_exists($name, $arguments)) {
                        if (is_numeric($arguments[$name])) {
                            $value = $arguments[$name] * 1;
                        } else {
                            $value = $arguments[$name];
                        }
                        $command->$name = $value;
                    }
                }
                $errors = $this->validator->validate($command);

                if (count($errors) > 0) {
                    $errorsString = (string)$errors;
                    throw new \InvalidArgumentException($errorsString);
                }

                $methodParameters[] = $command;
            } else {
                $methodParameters[] = $arguments[$parameter->getName()];
            }
        }

        return call_user_func_array([$this->object, $this->method], $methodParameters);
    }
} 
