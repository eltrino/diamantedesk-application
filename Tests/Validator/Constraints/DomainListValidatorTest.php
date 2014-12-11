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
