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
     */
    private $context;

    /**
     * @var \Symfony\Component\Validator\DefaultTranslator
     * @Mock \Symfony\Component\Validator\DefaultTranslator
     */
    private $defaultTranslator;

    /**
     * @var \Symfony\Component\Validator\ValidationVisitor
     * @Mock \Symfony\Component\Validator\ValidationVisitor
     */
    private $globalContext;

    /**
     * @var \Symfony\Component\Validator\ConstraintViolationList
     * @Mock \Symfony\Component\Validator\ConstraintViolationList
     */
    private $constraintViolationList;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->context = new ExecutionContext($this->globalContext, $this->defaultTranslator);
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

        $validator->validate($value, $constraint);

        $this->assertNull($this->context->getViolations());
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

        $violation = new ConstraintViolation(null, $constraint->message, array('%domain%' => 'eltrino.c'), null,'',null);

        $this->globalContext
            ->expects($this->atLeastOnce())
            ->method('getViolations')
            ->will($this->returnValue($this->constraintViolationList));

        $this->constraintViolationList
            ->expects($this->atLeastOnce())
            ->method('add')
            ->with($violation);

        $validator->validate($value, $constraint);

        $this->assertEquals($this->constraintViolationList, $this->context->getViolations());
    }
}
