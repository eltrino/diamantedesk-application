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
namespace Diamante\ApiBundle\Controller;

use Diamante\ApiBundle\Handler\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $id = $this->getRequest()->attributes->get('_service_id');
        $method = $this->getRequest()->attributes->get('_service_method');
        $service = $this->container->get($id);

        $routeParams = $this->getRequest()->attributes->get('_route_params');
        $routeParams = array_merge($routeParams, $this->getRequest()->request->all());
        $routeParams['properties'] = $this->getRequest()->request->all();

        $methodHandler = new Method($service, $method, $this->container->get('validator'));

        /** @var  $serializer */
        $serializer = $this->container->get('jms_serializer');

        return new Response($serializer->serialize($methodHandler->invoke($routeParams), $routeParams['_format']));
    }

}
