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

use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class HandleView
{

    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_diamante_api')) {
            return;
        }

        $data = $event->getControllerResult();
        $responseMethod = 'prepare' . ucfirst(strtolower($request->getMethod())) . 'Response';
        $event->setResponse($this->$responseMethod($data, $request->getRequestFormat()));
    }

    protected function prepareGetResponse($data, $format)
    {
        return new Response($this->serializer->serialize($data, $format), 200);
    }

    protected function preparePutResponse()
    {
        return new Response('', 200);
    }

    protected function preparePostResponse($data, $format)
    {
        return new Response($this->serializer->serialize($data, $format), 201);
    }

    protected function prepareDeleteResponse()
    {
        return new Response('', 204);
    }
} 
