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

namespace Diamante\DeskBundle\Tests\Validator\Constraints;

use Diamante\DeskBundle\Validator\Constraints\DomainList;
use Diamante\DeskBundle\Validator\Constraints\DomainListValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

class DomainListValidatorTest extends AbstractConstraintValidatorTest
{
    /**
     * @test
     */
    public function testValidate()
    {
        $constraint = new DomainList();
        $value = 'gmail.com, eltrino.com';

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function testValidateEmptyValue()
    {
        $constraint = new DomainList();
        $value = '';

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @test
     */
    public function testAddsViolationOnInvalidValue()
    {
        $constraint = new DomainList();
        $value = 'g.com, eltrino.c';

        $this->validator->validate($value, $constraint);
        $this->buildViolation($constraint->message)
            ->setParameter('%domain%', 'eltrino.c')
            ->assertRaised();
    }

    public function createValidator()
    {
        return new DomainListValidator();
    }
}
