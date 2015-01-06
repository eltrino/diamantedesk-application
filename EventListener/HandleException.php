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

use FOS\RestBundle\Util\Codes;
use JMS\Serializer\Serializer;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class HandleException
{

    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_diamante_rest_service')) {
            return;
        }

        $exception = $event->getException();
        if ($exception instanceof ForbiddenException) {
            $event->setResponse(new Response(
                $this->serializer->serialize(['error' => $exception->getMessage()],
                    $request->getRequestFormat()), Codes::HTTP_FORBIDDEN
            ));
        } else if ($exception instanceof \RuntimeException) {
            $event->setResponse(new Response(
                $this->serializer->serialize(['error' => $exception->getMessage()],
                    $request->getRequestFormat()), Codes::HTTP_NOT_FOUND
            ));
        }
    }

} 
