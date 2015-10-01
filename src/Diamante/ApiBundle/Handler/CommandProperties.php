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

namespace Diamante\ApiBundle\Handler;

class CommandProperties
{
    private $class;

    private $object;

    public function __construct(\ReflectionClass $class)
    {
        $this->class = $class;
        $this->object = new $class->name;
    }

    public function map(array $values)
    {
        $properties = $this->class->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if (isset($values[$property->name])) {
                $value = $values[$property->name];
                $propertyType = $this->getPropertyType($property);
                if (($propertyType == 'integer' || $propertyType == 'int') && is_numeric($value)) {
                    $value = $value * 1;
                }
                $this->object->{$property->name} = $value;
            }
        }

        return $this->object;
    }

    protected function getPropertyType(\ReflectionProperty $property)
    {
        $docComment = $property->getDocComment();
        preg_match("/type=\"(.*?)\"/", $docComment, $ms);
        return count($ms) > 1 ? $ms[1] : null;
    }
} 
