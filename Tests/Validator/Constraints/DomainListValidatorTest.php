<?php

namespace Diamante\DeskBundle\Tests\Validator\Constraints;

use Diamante\DeskBundle\Validator\Constraints\DomainList;
use Diamante\DeskBundle\Validator\Constraints\DomainListValidator;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ExecutionContext;

class DomainListValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Symfony\Component\Validator\ExecutionContext
     * @Mock \Symfony\Component\Validator\ExecutionContext
     */
    private $context;


    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    /**
     * @test
     */
    public function testValidate()
    {
        $constraint = new DomainList();
        $value = 'gmail.com, eltrino.com';

        $validator = new DomainListValidator();
        $validator->initialize($this->context);

        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $validator->validate($value, $constraint);
    }

    /**
     * @test
     */
    public function testAddsViolationOnInvalidValue()
    {
        $constraint = new DomainList();
        $value = 'g.com, eltrino.c';

        $validator = new DomainListValidator();
        $validator->initialize($this->context);

        $this->context
            ->expects($this->once())
            ->method('addViolation')
            ->with($this->equalTo($constraint->message), $this->equalTo(array("%domain%" => 'eltrino.c')));

        $validator->validate($value, $constraint);
    }
}
