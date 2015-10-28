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

namespace Diamante\AutomationBundle\Rule\Condition;

class ConditionFactory
{
    const CONDITION_PARSE_FORMAT = '/^(eq|neq|not|in|nin|lt|lte|gt|gte|contains|created)(\[([A-Za-z]+)\,\s+(.*)\])?/';

    /**
     * @param $string
     * @return Condition
     * @throws \Exception
     */
    public static function getConditionFor($string)
    {
        $attributes = static::parse($string);
        $class = sprintf("%s\\Specific\\%s", __NAMESPACE__, ucfirst($attributes->type));

        if (!class_exists($class)) {
            throw new \Exception(sprintf("Unknown condition used: %s", (string)$attributes->type));
        }

        return new $class($attributes->property, $attributes->value);
    }

    /**
     * @param $string
     * @return \StdClass
     */
    protected static function parse($string)
    {
        $attributes = $matches = [];

        $result = preg_match(self::CONDITION_PARSE_FORMAT, $string, $matches);

        if ($result) {
            $attributes['type']     = $matches[1];
            $attributes['property'] = isset($matches[3]) ? $matches[3] : null;
            $attributes['value'] = isset($matches[4]) ? $matches[4] : null;
        }

        return (object)$attributes;
    }
}