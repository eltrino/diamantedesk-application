<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Rule\Condition;


use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConditionFactory
{
    const METHOD_CALL_SEPARATOR = "::";

    /**
     * @var ParameterBag
     */
    protected $conditions;

    /**
     * @var AutomationConfigurationProvider
     */
    protected $configProvider;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ConditionFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->configProvider = $container->get('diamante_automation.config.provider');
        $this->container      = $container;
    }

    /**
     * @param $type
     * @param array $parameters
     * @param $entityType
     * @return ConditionInterface
     */
    public function getCondition($type, array $parameters, $entityType)
    {
        $this->conditions = $this->configProvider->getConfiguredConditions();

        if (!$this->conditions->has($type)) {
            throw new \RuntimeException(sprintf("Unknown condition type %s", $type));
        }

        $class = $this->conditions->get(sprintf("%s.class", $type));

        if (!class_exists($class)) {
            throw new \RuntimeException(
                sprintf(
                    "Invalid class configuration for condition '%s'. Class '%s' does not exist",
                    $type,
                    $class
                    )
            );
        }

        $property = key($parameters);
        $expectedValue = $parameters[$property];

        $refClass = new \ReflectionClass($class);

        if ($refClass->isSubclassOf('\\Diamante\\AutomationBundle\\Rule\\Action\\Condition\\AbstractAccessorAwareCondition')) {
            list($accessor, $accessorMethod) = $this->resolveAccessor($entityType, $property);

            return new $class($property, $expectedValue, $accessor, $accessorMethod);
        }

        return new $class($property, $expectedValue);
    }

    protected function resolveAccessor($entityType, $property)
    {
        $entity = $this->configProvider->getEntityConfiguration($entityType);

        if (!$entity->has('properties.'.$property)
            ||!$entity->has('properties.'.$property.'.accessor')) {
            throw new \RuntimeException("Invalid configuration for property accessor");
        }

        $accessorConfig = $entity->get('properties.'.$property.'.accessor');

        list($accessorServiceName, $accessorMethod) = explode(self::METHOD_CALL_SEPARATOR, $accessorConfig);

        if (!$this->container->has($accessorServiceName)) {
            throw new \RuntimeException("Invalid configuration for property accessor");
        }

        $accessor = $this->container->get($accessorServiceName);

        if (!method_exists($accessor, $accessorMethod)) {
            throw new \RuntimeException("Invalid configuration for property accessor");
        }

        return [$accessor, $accessorMethod];
    }
}