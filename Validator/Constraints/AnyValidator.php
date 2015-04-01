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

class AnyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $violationsBefore = $this->context->getViolations()->getIterator()->getArrayCopy();

        if (null === $value) {
            return;
        }

        foreach ($constraint->constraints as $constraintEntity) {
            $validatedBy = $constraintEntity->validatedBy();
            $validator = new $validatedBy();
            $validator->initialize($this->context);
            $validator->validate($value, $constraintEntity);
        }

        $violationsAfter = $this->context->getViolations()->getIterator()->getArrayCopy();

        if (count($constraint->constraints) == (count($violationsAfter) - count($violationsBefore))) {
            $this->context->addViolation($constraint->message);
            return;
        }

        $diff = array_diff_assoc($violationsAfter, $violationsBefore);

        foreach ($diff as $key=>$value) {
            $this->context->getViolations()->remove($key);
        }
    }
}
