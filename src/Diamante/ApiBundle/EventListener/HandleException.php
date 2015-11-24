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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Diamante\DeskBundle\Model\Ticket\Exception\TicketMovedException;
use Diamante\DeskBundle\Model\Branch\Exception\BranchHasTicketsException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HandleException
{

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var $container
     */
    private $container;

    /**
     * @param Serializer         $serializer
     * @param ContainerInterface $container
     */
    public function __construct(Serializer $serializer, ContainerInterface $container)
    {
        $this->serializer = $serializer;
        $this->container = $container;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_diamante_rest_service')) {
            return;
        }

        $exception = $event->getException();
        $event->setResponse(
            $this->getFormattedResponse($request, $exception, $this->getStatusCode($exception))
        );
    }

    /**
     * @param \Exception $exception
     * @return int
     */
    protected function getStatusCode(\Exception $exception)
    {
        switch (true) {
            case $exception instanceof TicketMovedException:
                return Codes::HTTP_MOVED_PERMANENTLY;

            case $exception instanceof ForbiddenException:
                return Codes::HTTP_FORBIDDEN;

            case $exception instanceof Exception\EntityNotFoundException:
                return Codes::HTTP_NOT_FOUND;

            case $exception instanceof Exception\ValidationException:
                return Codes::HTTP_BAD_REQUEST;

            case $exception instanceof \RuntimeException:
                return Codes::HTTP_NOT_FOUND;

            case $exception instanceof BranchHasTicketsException:
                return Codes::HTTP_CONFLICT;

            default:
                return Codes::HTTP_INTERNAL_SERVER_ERROR;
        }
    }

    /**
     * @param Request $request
     * @param $exception
     * @param $httpCode
     * @return Response
     */
    protected function getFormattedResponse(Request $request, $exception, $httpCode)
    {
        if (Codes::HTTP_MOVED_PERMANENTLY == $httpCode) {
            $response = new Response(null, $httpCode);
            $response->headers->set('X-Location',
                $this->container->get('router')->generate(
                    'diamante_ticket_api_service_diamante_load_ticket_by_key',
                    ['key' => $exception->getTicketKey()]
                ));
            return $response;
        }
        return new Response(
            $this->serializer->serialize(['error' => $exception->getMessage()],
                $request->getRequestFormat()), $httpCode
        );
    }

} 
