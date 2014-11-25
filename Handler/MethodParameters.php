<?php

namespace Diamante\ApiBundle\Handler;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\Validator;

class MethodParameters
{
    private $method;
    private $validator;
    private $data = [];

    public function __construct(\ReflectionMethod $method, Validator $validator)
    {
        $this->method = $method;
        $this->validator = $validator;
    }

    public function addParameterBag(ParameterBag $bag)
    {
        $this->data = array_merge($this->data, $bag->all());
    }

    public function putIn(ParameterBag $bag)
    {
        $parameters = $this->method->getParameters();

        foreach ($parameters as $parameter) {
            if ($bag->has($parameter->getName())) {
                continue;
            }

            if ($parameter->getClass()) {
                $mapper = new CommandProperties($parameter->getClass());
                $command = $mapper->map($this->data);

                $errors = $this->validator->validate($command);

                if (count($errors) > 0) {
                    $errorsString = (string)$errors;
                    throw new \InvalidArgumentException($errorsString);
                }

                $bag->set($parameter->getName(), $command);
            } elseif (array_key_exists($parameter->getName(), $this->data)) {
                $bag->set($parameter->getName(), $this->data[$parameter->getName()]);
            }
        }
    }
} 
