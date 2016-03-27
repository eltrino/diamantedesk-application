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

namespace Diamante\AutomationBundle\Infrastructure\Shared;

/**
 * Class TargetMapper
 *
 * @package Diamante\AutomationBundle\Infrastructure\Shared
 */
class TargetMapper
{
    /**
     * @param array $changeset
     *
     * @return array
     */
    public static function fromChangeset(array $changeset)
    {
        $target = [];

        foreach($changeset as $property => $values) {
            list($outdated, $actual) = $values;
            $target[$property] = $actual;
        }

        return $target;
    }

    /**
     * @param object $entity
     *
     * @return array
     */
    public static function fromEntity($entity)
    {
        $target = [];
        $reflect = new \ReflectionClass($entity);
        $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        foreach($props as $refProperty) {
            $refProperty->setAccessible(true);
            $name = $refProperty->getName();
            $value = $refProperty->getValue($entity);
            $target[$name] = $value;
        }

        return $target;
    }
}