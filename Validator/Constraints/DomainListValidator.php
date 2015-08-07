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

class DomainListValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @return bool|null
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value) || null === $value) {
            return;
        }
        $domains = explode(',', $value);

        foreach ($domains as $domain)
        {
            $domain = trim($domain);
            $result = preg_match('/^((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.)+[A-Za-z]{2,13}$/', $domain);

            if (!(bool)$result) {
                $this->context->addViolation(
                    $constraint->message,
                    array("%domain%"=>$domain)
                );
            }
        }
    }
}
