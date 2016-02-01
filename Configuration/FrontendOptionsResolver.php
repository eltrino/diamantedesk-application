<?php
/*
 * Copyright (c) 2016 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Configuration;


use Symfony\Component\DependencyInjection\ContainerInterface;

class FrontendOptionsResolver
{
    const RESOLVE_MARKER_SERVICE   = '@';
    const RESOLVE_MARKER_CLASS     = '^';
    const RESOLVE_MARKER_PARAMETER = '%';
    const RESOLVE_MARKER_ROUTE     = '>';

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function resolve($configuration)
    {
        if (is_array($configuration)) {
            return $configuration;
        }

        $marker = substr($configuration, 0, 1);

        switch ($marker) {
            case self::RESOLVE_MARKER_ROUTE:
                $data = trim($configuration, self::RESOLVE_MARKER_ROUTE);
                break;
            case self::RESOLVE_MARKER_CLASS:
                $data = $this->resolveFromClass($configuration);
                break;
            case self::RESOLVE_MARKER_PARAMETER:
                $data = $this->resolveFromContainerParameter($configuration);
                break;
            case self::RESOLVE_MARKER_SERVICE:
                $data = $this->resolveFromContainerService($configuration);
                break;
            default:
                throw new \RuntimeException("Invalid configuration");
                break;
        }

        return $data;
    }

    protected function resolveFromClass($config)
    {
        $config = trim($config, self::RESOLVE_MARKER_CLASS);

        list($class, $method) = explode('::', $config);

        if (!class_exists($class)) {
            throw new \RuntimeException("Invalid configuration");
        }

        if (!method_exists($class, $method)) {
            throw new \RuntimeException("Invalid configuration");
        }

        $result = call_user_func([$class, $method]);

        return $result;
    }

    protected function resolveFromContainerParameter($config)
    {
        $config = trim($config, self::RESOLVE_MARKER_PARAMETER);

        if ($this->container->hasParameter($config)) {
            return $this->container->getParameter($config);
        }

        return null;
    }

    protected function resolveFromContainerService($config)
    {
        $result = null;

        $config = trim($config, self::RESOLVE_MARKER_SERVICE);

        list($service, $method) = explode('->', $config);

        try {
            if ($this->container->has($service)) {
                $service = $this->container->get($service);
            }

            if (method_exists($service, $method)) {
                $result = call_user_func([$service, $method]);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException("Invalid configuration");
        }

        return $result;
    }
}