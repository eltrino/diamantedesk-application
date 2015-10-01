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

use Diamante\DeskBundle\Validator\Constraints\PrintType;
use Diamante\DeskBundle\Validator\Constraints\PrintTypeValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;

class PrintTypeValidatorTest extends AbstractConstraintValidatorTest
{


    protected function createValidator() {
        return new PrintTypeValidator();
    }

    /**
     * @param $value
     *
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $constraint = new PrintType();

        $this->validator->validate($value, $constraint);
        $this->assertNoViolation();
    }

    /**
     * @param $value
     *
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $constraint = new PrintType();
        $this->validator->validate($value, $constraint);
        $this->assertCount(1, $this->context->getViolations());
    }

    public static function getValidValues()
    {
        return [
            ['valid value'],
            ['123']
        ];
    }

    public static function getInvalidValues()
    {
        return [
            ["invalid value\n"],
            ["invalid value\t"]
        ];
    }
}
