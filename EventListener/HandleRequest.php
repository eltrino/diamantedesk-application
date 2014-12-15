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

use Diamante\ApiBundle\Handler\MethodParameters;
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
        $controller = $event->getController();

        if (is_array($controller)) {
            if (!$controller[0] instanceof RestServiceInterface) {
                return;
            }
            $reflection = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            if (!$controller instanceof RestServiceInterface) {
                return;
            }
            $reflection = new \ReflectionObject($controller);
            $reflection = $reflection->getMethod('__invoke');
        } else {
            return;
        }

        $request->attributes->set('_diamante_rest_service', true);

        $methodParameters = new MethodParameters($reflection, $this->validator);
        if ($request->request->count()) {
            $request->attributes->set('properties', $request->request->all());
        }
        $methodParameters->addParameterBag($request->request);
        $methodParameters->addParameterBag($request->attributes);
        $methodParameters->putIn($request->attributes);
    }
}
