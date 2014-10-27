<?php

namespace Diamante\DeskBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DomainListValidator extends ConstraintValidator
{
    /**
     * @param mixed $value
     * @param Constraint $constraint
     *
     * @return bool
     */
    public function validate($value, Constraint $constraint)
    {
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
