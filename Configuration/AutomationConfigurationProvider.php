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

namespace Diamante\AutomationBundle\Configuration;

use Diamante\AutomationBundle\Exception\InvalidConfigurationException;
use Diamante\AutomationBundle\Infrastructure\Shared\ParameterBag;
use Diamante\AutomationBundle\Model\Group;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\Translator;

class AutomationConfigurationProvider
{
    const DATA_TYPE_WILDCARD = '*';

    protected $entities     = [];
    protected $conditions   = [];
    protected $actions      = [];

    protected $targetMap    = [];

    protected static $configStructure     = ['entities', 'conditions', 'actions'];

    protected $connectorsMap = [
        Group::CONNECTOR_EXCLUSIVE => 'diamante.automation.connector.exclusive',
        Group::CONNECTOR_INCLUSIVE => 'diamante.automation.connector.inclusive'
    ];

    public function __construct(ContainerInterface $container)
    {
        foreach (static::$configStructure as $section) {
            $paramName = sprintf("diamante.automation.config.%s", $section);
            if ($container->hasParameter($paramName)) {
                $this->$section = array_merge($this->$section, $container->getParameter($paramName));
            }
        }

        $this->rebuildTargetMap();
    }

    public function getEntityConfiguration($target)
    {
        if (!array_key_exists($target, $this->entities)) {
            throw new InvalidConfigurationException(sprintf("Requested entity '%s' is not configured.", $target));
        }

        return new ParameterBag($this->entities[$target]);
    }

    public function getTargetByClass($object)
    {
        $className = get_class($object);

        foreach ($this->targetMap as $target => $class) {
            if ($class === $className) {
                return $target;
            }
        }

        return null;
    }

    public function getConfiguredEntities($asArray = false)
    {
        if ($asArray) {
            return $this->entities;
        }

        return new ParameterBag($this->entities);
    }

    public function getConfiguredConditions($asArray = false)
    {
        if ($asArray) {
            return $this->conditions;
        }

        return new ParameterBag($this->conditions);
    }

    public function getConfiguredActions($asArray = false)
    {
        if ($asArray) {
            return $this->actions;
        }

        return new ParameterBag($this->actions);
    }

    protected function rebuildTargetMap()
    {
        foreach ($this->entities as $name => $config) {
            if (!array_key_exists('class', $config) || empty($config['class'])) {
                throw new InvalidConfigurationException(sprintf("Entity '%s' should have class configured", $name));
            }

            $this->targetMap[$name] = $config['class'];
        }
    }

    /**
     * @return array
     */
    public static function getConfigStructure()
    {
        return static::$configStructure;
    }

    /**
     * @param $entity
     * @param $property
     * @return array
     */
    protected function getActionsForProperty($entity, $property)
    {
        $actions = [];

        $config = $this->getEntityConfiguration($entity);

        if (!$config->get(sprintf("properties.%s", $property))) {
            return $actions;
        }

        $propertyDataType = $config->get(sprintf('properties.%s.type', $property));


        foreach ($this->actions as $actionName => $definition) {
            if (in_array(sprintf("!%s", $propertyDataType), $definition['data_types'])) {
                continue;
            }

            if (in_array(self::DATA_TYPE_WILDCARD, $definition['data_types'])) {
                $actions[] = $actionName;
                continue;
            }

            if (in_array($propertyDataType, $definition['data_types'])) {
                $actions[] = $actionName;
            }
        }

        return $actions;
    }

    /**
     * @param Translator $translator
     * @return array
     */
    public function prepareConfigDump(Translator $translator)
    {
        $config = [];

        $config['entities']   = $this->dumpEntitiesConfig($translator);
        $config['conditions'] = $this->dumpConditionsConfig($translator);
        $config['actions']    = $this->dumpActionsConfig($translator);
        $config['connectors'] = $this->dumpConnectorsConfig($translator);

        return $config;
    }

    /**
     * @param Translator $translator
     * @return array
     */
    protected function dumpEntitiesConfig(Translator $translator)
    {
        $entities = [];

        foreach ($this->entities as $name => $config) {
            $entity = [
                'label' => $translator->trans($config['frontend_label'])
            ];

            foreach ($config['properties'] as $propertyName => $propertyConfig) {
                $property = [
                    'label'   => $translator->trans($propertyConfig['frontend_label']),
                    'type'    => $propertyConfig['type'],
                    'actions' => $this->getActionsForProperty($name, $propertyName),
                ];

                $entity['properties'][$propertyName] = $property;
            }

            $entities[$name] = $entity;

            unset($entity, $property, $name, $config, $propertyName, $propertyConfig);
        }

        return $entities;
    }

    /**
     * @param Translator $translator
     * @return array
     */
    protected function dumpConditionsConfig(Translator $translator)
    {
        $conditions = [];

        foreach ($this->conditions as $name => $config) {
            $conditions[$name] = $translator->trans($config['frontend_label']);
        }

        return $conditions;
    }

    /**
     * @param Translator $translator
     * @return array
     */
    protected function dumpActionsConfig(Translator $translator)
    {
        $actions = [];

        foreach ($this->actions as $name => $config) {
            $actions[$name] = [
                "label"      => $translator->trans($config['frontend_label']),
                "data_types" => $config['data_types']
            ];
        }

        return $actions;
    }

    protected function dumpConnectorsConfig(Translator $translator)
    {
        $connectors = [];

        foreach ($this->connectorsMap as $name => $label) {
            $connectors[strtolower($name)]  = $translator->trans($label);
        }

        return $connectors;
    }
}