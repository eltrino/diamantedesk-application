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

use Diamante\DeskBundle\Validator\Constraints\Entity;
use Diamante\DeskBundle\Validator\Constraints\EntityValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

class EntityValidatorTest extends AbstractConstraintValidatorTest
{
    protected function createValidator()
    {
        return new EntityValidator();
    }

    /**
     * @dataProvider getValidValues
     */
    public function testNullIsValid($value)
    {
        $constraint = new Entity();
        $this->validator->validate(null, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $constraint = new Entity();
        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $constraint = new Entity();
        $this->validator->validate($value, $constraint);
        $this->buildViolation($constraint->message)
            ->assertRaised();
    }

    public static function getValidValues()
    {
        return [
            ['1'],
            [3],
            [new \stdClass()]
        ];
    }

    public static function getInvalidValues()
    {
        return [
            ['g5'],
            [true],
            ['false'],
            [[]]
        ];
    }
}
