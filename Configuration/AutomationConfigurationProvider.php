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

class AutomationConfigurationProvider
{
    protected $entities     = [];
    protected $conditions   = [];
    protected $actions      = [];

    protected $targetMap    = [];

    protected static $configStructure     = ['entities', 'conditions', 'actions'];

    public function setConfiguration(array $configuration)
    {
        foreach (array_keys(static::$configStructure) as $section) {
            if (array_key_exists($section, $configuration)) {
                $this->$section = array_merge($this->$section, $configuration[$section]);
            }
        }

        $this->rebuildTargetMap();
    }

    public function getEntityConfiguration($target)
    {
        if (!array_key_exists($target, $this->entities)) {
            throw new InvalidConfigurationException(sprintf("Requested entity '%s' is not configured.", $target));
        }

        return $this->entities[$target];
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
        foreach ($this->entities as $name=>$config) {
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
}