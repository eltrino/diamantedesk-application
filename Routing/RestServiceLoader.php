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

namespace Diamante\ApiBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RestServiceLoader extends Loader
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        $service = $this->container->get($resource);
        $class = get_class($service);

        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if (preg_match(
                '/@api\s\{(get|post|put|delete)\}\s([^\s]+)\s.*/',
                $reflectionMethod->getDocComment(),
                $matches
            )
            ) {
                $methods = [$matches[1]];

                $defaults = [
                    '_controller' => $resource . ':' . $reflectionMethod->getName(),
                    '_format' => 'json',
                    '_diamante_api' => true
                ];

                $requirements = [
                    '_format' => 'json|xml'
                ];

                $collection->add(
                    $this->routeId($resource, $reflectionMethod->getName()),
                    new Route($matches[2] . '.{_format}', $defaults, $requirements, [], '', [], $methods)
                );
            }
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'rest_service';
    }

    private function routeId($serviceId, $serviceMethod)
    {
        return sprintf('%s_%s',
            str_replace('.', '_', $serviceId),
            strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $serviceMethod))
        );
    }
}
