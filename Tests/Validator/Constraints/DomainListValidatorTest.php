<?php

namespace Diamante\DeskBundle\Tests\Validator\Constraints;

use Diamante\DeskBundle\Validator\Constraints\DomainList;
use Diamante\DeskBundle\Validator\Constraints\DomainListValidator;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ExecutionContext;
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
