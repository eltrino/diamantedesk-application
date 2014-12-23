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

use Diamante\DeskBundle\Model\Shared\Entity;
use FOS\Rest\Util\Codes;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
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

        if (!$request->attributes->has('_diamante_rest_service')) {
            return;
        }

        $data = $event->getControllerResult();
        $responseMethod = strtolower($request->getMethod());

        if (!method_exists($this, $responseMethod)) {
            throw new \RuntimeException(sprintf('Can not handle response for method "%s".', $responseMethod));
        }

        $response = call_user_func_array([$this, $responseMethod], [$data, $request->getRequestFormat(), $request]);
        $event->setResponse($response);
    }

    /**
     * Prepare response for HTTP GET request, with standard HTTP status code and formatted body.
     *
     * @param Entity|array $data
     * @param $format
     * @return Response
     */
    protected function get($data, $format)
    {
        $groups = ['Default'];
        if (is_array($data)) {
            $groups[] = 'list';
        } else {
            $groups[] = 'entity';
        }

        return new Response(
            $this->serializer->serialize(
                $data,
                $format,
                SerializationContext::create()->setGroups($groups)
            ),
            Codes::HTTP_OK
        );
    }

    /**
     * Prepare response for HTTP PUT request, with standard HTTP status code and empty body.
     *
     * @return Response
     */
    protected function put()
    {
        return new Response('', Codes::HTTP_OK);
    }

    /**
     * Prepare response for HTTP POST request, with standard HTTP status code, location header and formatted body.
     *
     * @param Entity $data
     * @param $format
     * @param Request $request
     * @return Response
     */
    protected function post(Entity $data, $format, Request $request)
    {
        $pathInfo = $this->entityLocation($request->getPathInfo());
        $location = $request->getUriForPath(sprintf($pathInfo, $data->getId()));
        $context = SerializationContext::create()->setGroups(['Default', 'entity']);

        return new Response(
            $this->serializer->serialize($data, $format, $context),
            Codes::HTTP_CREATED,
            ['Location' => $location]
        );
    }

    /**
     * Prepare response for HTTP PATCH request, with standard HTTP status code and empty body.
     *
     * @return Response
     */
    protected function patch()
    {
        return new Response('', Codes::HTTP_OK);
    }

    /**
     * Prepare response for HTTP DELETE request, with standard HTTP status code and empty body.
     * @return Response
     */
    protected function delete()
    {
        return new Response('', Codes::HTTP_NO_CONTENT);
    }

    /**
     * Generate uri for Location header.
     * Uses request uri as template and adds entity identifier to it.
     *
     * @param $requestUri
     * @return string
     */
    private function entityLocation($requestUri)
    {
        $position = strpos($requestUri, '.');
        if ($position !== FALSE) {
            return substr($requestUri, 0 , $position) . '/%d' . substr($requestUri, $position);
        } else {
            return $requestUri . '/%d';
        }
    }
} 
