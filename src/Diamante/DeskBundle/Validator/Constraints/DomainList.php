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