<?php

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
                if (is_numeric($value)) {
                    $value = $value * 1;
                }
                $this->object->{$property->name} = $value;
            }
        }

        return $this->object;
    }
} 
