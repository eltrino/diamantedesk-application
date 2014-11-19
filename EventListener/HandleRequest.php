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

namespace Diamante\ApiBundle\EventListener;

use Diamante\ApiBundle\Routing\RestServiceInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Validator\Validator;

class HandleRequest
{

    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_diamante_rest_service')) {
            return;
        }

        $reflection = $this->getReflectionMethod($event->getController());

        $parameters = $reflection->getParameters();
        if (count($parameters) == 1 && $parameters[0]->getClass()) {
            $commandData = $request->attributes->get('_route_params');
            $commandData = array_merge($commandData, $request->request->all());
            // @todo find possible solution to avoid this hardcoded parameter
            $commandData['properties'] = $request->request->all();

            $commandClassName = $parameters[0]->getClass()->name;
            $command = new $commandClassName;
            $commandProperties = get_class_vars($commandClassName);
            foreach ($commandProperties as $name => $value) {
                if (array_key_exists($name, $commandData)) {
                    if (is_numeric($commandData[$name])) {
                        $value = $commandData[$name] * 1;
                    } else {
                        $value = $commandData[$name];
                    }
                    $command->$name = $value;
                }
                // @todo handle files
            }

            $errors = $this->validator->validate($command);

            if (count($errors) > 0) {
                $errorsString = (string)$errors;
                throw new \InvalidArgumentException($errorsString);
            }

            $event->getRequest()->attributes->set($parameters[0]->getName(), $command);
        }
    }

    private function getReflectionMethod($controller)
    {
        if (is_array($controller)) {
            return new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $reflection = new \ReflectionObject($controller);
            return $reflection->getMethod('__invoke');
        } else {
            return false;
        }
    }
}
