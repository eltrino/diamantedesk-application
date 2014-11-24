<?php

namespace Diamante\DeskBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class DomainList
 * @package Diamante\DeskBundle\Validator\Constraints
 * @Annotation
 */
class DomainList extends Constraint
{
    public $message = 'Domain "%domain%" does not appear to be a valid domain';

    public function validatedBy()
    {
        return get_class($this) . 'Validator';
    }
}