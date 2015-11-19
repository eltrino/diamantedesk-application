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

namespace Diamante\AutomationBundle\Rule\Action\Entity;

class ActionFactory
{
    public static function create($command)
    {
        if(is_null($command->type)) {
            return null;
        }

        $class = self::getClass($command->type);
        $classInstance = $class::getInstance();
        return $classInstance->create($command);
    }

    public static function parse($string)
    {
        $matches = [];
        $result = preg_match('/^(\w+)\[.*?\]$/', $string, $matches);

        if (!$result) {
            return null;
        }

        $class = self::getClass($matches[1]);
        $classInstance = $class::getInstance();

        return $classInstance->parse($string);
    }

    protected static function getClass($name)
    {
        $class = sprintf("%s\\Specific\\%s", __NAMESPACE__, $name);

        if (!class_exists($class)) {
            throw new \Exception(sprintf("Unknown action used: %s", $name));
        }

        return $class;
    }
}