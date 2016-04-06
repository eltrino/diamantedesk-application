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
use FOS\RestBundle\Util\Codes;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class HandleView
{
    private $serializer;
    private $logger;

    public function __construct(Serializer $serializer, ContainerInterface $container, Logger $logger)
    {
        $this->serializer = $serializer;
        $this->container  = $container;
        $this->logger     = $logger;
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
            $this->logger->error(sprintf('Invalid method provided: %s', $responseMethod));
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
        $headers = $this->container->get('diamante.api.headers.container')->allPreserveCase();

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
            Codes::HTTP_OK,
            $headers
        );
    }

    /**
     * Prepare response for HTTP PUT request, with standard HTTP status code and empty body.
     *
     * @param Entity $data
     * @param $format
     * @return Response
     */
    protected function put(Entity $data = null, $format)
    {
        $context = SerializationContext::create()->setGroups(['Default', 'entity']);
        if (is_null($data)) {
            $body = '';
            $httpCode = Codes::HTTP_NO_CONTENT;
        } else {
            $body = $this->serializer->serialize($data, $format, $context);
            $httpCode = Codes::HTTP_OK;
        }
        return new Response($body, $httpCode);
    }

    /**
     * Prepare response for HTTP POST request, with standard HTTP status code, location header and formatted body.
     *
     * @param Entity $data
     * @param $format
     * @param Request $request
     * @return Response
     */
    protected function post($data = null, $format, Request $request)
    {
        $pathInfo = $this->entityLocation($request->getPathInfo());

        if (is_null($data)) {
            $headers = [];
            $body = '';
        } elseif (is_array($data)) {
            $headers = [];
            $context = SerializationContext::create()->setGroups(['Default', 'list']);
            $body = $this->serializer->serialize($data, $format, $context);
        } else {
            $headers = ['Location' => $request->getUriForPath(sprintf($pathInfo, $data->getId()))];
            $context = SerializationContext::create()->setGroups(['Default', 'entity']);
            $body = $this->serializer->serialize($data, $format, $context);
        }
        return new Response($body, Codes::HTTP_CREATED, $headers);
    }

    /**
     * Prepare response for HTTP PATCH request, with standard HTTP status code and empty body.
     *
     * @param Entity $data
     * @param $format
     * @return Response
     */
    protected function patch(Entity $data = null, $format)
    {
        $context = SerializationContext::create()->setGroups(['Default', 'entity']);
        if (is_null($data)) {
            $body = '';
            $httpCode = Codes::HTTP_NO_CONTENT;
        } else {
            $body = $this->serializer->serialize($data, $format, $context);
            $httpCode = Codes::HTTP_OK;
        }
        return new Response($body, $httpCode);
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
     * @param string $requestUri
     * @return string
     */
    private function entityLocation($requestUri)
    {
        $position = strpos($requestUri, '.');
        if ($position !== FALSE) {
            return substr($requestUri, 0, $position) . '/%d' . substr($requestUri, $position);
        } else {
            return $requestUri . '/%d';
        }
    }
} 
