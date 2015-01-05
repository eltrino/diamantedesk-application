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
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints\TypeValidator;

class AnyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $allowedTypes = '';
        $validator = new TypeValidator();
        $validator->initialize($this->context);
        foreach ($constraint->types as $constraintType) {
            $allowedTypes .= $constraintType->type . ', ';
            $validator->validate($value, $constraintType);
        }

        $violationsCount = $this->context->getViolations()->count();
        if (count($constraint->types) == $violationsCount) {
            $this->context->addViolation($constraint->message, array(
                '{{ value }}' => $this->formatValue($value),
                '{{ types }}'  => trim($allowedTypes, ', '),
            ));
        } else {
            for ($i = 0; $i < $violationsCount; $i++) {
                $this->context->getViolations()->remove($i);
            }
        }
    }
}
