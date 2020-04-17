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


use Doctrine\Common\Persistence\ObjectRepository;
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

    /**
     * @param array $configuration
     *
     * @return array|mixed|null|string
     */
    public function resolve(array $configuration)
    {
        if (!array_key_exists('source', $configuration)) {
            throw new \RuntimeException('Source type not specified');
        }

        $source = $configuration['source'];

        $marker = substr($source, 0, 1);

        switch ($marker) {
            case self::RESOLVE_MARKER_ROUTE:
                $data = trim($source, self::RESOLVE_MARKER_ROUTE);
                break;
            case self::RESOLVE_MARKER_CLASS:
                $data = $this->resolveFromClass($source);
                break;
            case self::RESOLVE_MARKER_PARAMETER:
                $data = $this->resolveFromContainerParameter($source);
                break;
            case self::RESOLVE_MARKER_SERVICE:
                $propertyList = null;

                if (array_key_exists('property_list', $configuration)) {
                    $propertyList = $configuration['property_list'];
                }

                $data = $this->resolveFromContainerService($source, $propertyList);
                break;
            default:
                throw new \RuntimeException('Invalid configuration');
                break;
        }

        return $data;
    }

    protected function resolveFromClass($config)
    {
        $config = trim($config, self::RESOLVE_MARKER_CLASS);
        [$class, $method] = explode('::', $config);
        $class = str_replace('\\\\', '\\', $class);

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

    protected function resolveFromContainerService($config, $propertyList = null)
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

        if ($service instanceof ObjectRepository) {

            if (empty($propertyList)) {
                throw new \RuntimeException('Property list can\'t be empty.');
            }

            $filtered = [];

            foreach ($result as $row) {
                $filteredItem = [];

                foreach ($propertyList as $property) {
                    $method = sprintf('get%s', ucfirst($property));
                    $filteredItem[$property] = $row->$method();
                }

                $filtered[] = $filteredItem;
            }

            return $filtered;
        }

        return $result;
    }
}