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

use Diamante\DeskBundle\Model\Entity\Exception;
use FOS\RestBundle\Util\Codes;
use JMS\Serializer\Serializer;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
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

        $event->setResponse(
            $this->getFormattedResponse($request, $exception->getMessage(), $this->getStatusCode($exception))
        );
    }

    /**
     * @param \Exception $exception
     * @return int
     */
    protected function getStatusCode(\Exception $exception)
    {
        switch (true) {
            case $exception instanceof ForbiddenException:
                return Codes::HTTP_FORBIDDEN;

            case $exception instanceof Exception\EntityNotFoundException:
                return Codes::HTTP_NOT_FOUND;

            case $exception instanceof Exception\ValidationException:
                return Codes::HTTP_BAD_REQUEST;

            case $exception instanceof \RuntimeException:
                return Codes::HTTP_NOT_FOUND;

            default:
                return Codes::HTTP_INTERNAL_SERVER_ERROR;
        }
    }

    /**
     * @param Request $request
     * @param $message
     * @param $httpCode
     * @return Response
     */
    protected function getFormattedResponse(Request $request, $message, $httpCode)
    {
        return new Response(
            $this->serializer->serialize(['error' => $message],
                $request->getRequestFormat()), $httpCode
        );
    }

} 
