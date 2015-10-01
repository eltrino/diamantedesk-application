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

use Diamante\DeskBundle\Validator\Constraints\Any;
use Diamante\DeskBundle\Validator\Constraints\AnyValidator;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\IsNull;		
use Symfony\Component\Validator\Constraints\NotNull;		
use Symfony\Component\Validator\Constraints\File;

class AnyValidatorTest extends AbstractConstraintValidatorTest
{

    /**
     * @return AnyValidator
     */
    protected function createValidator() {
        return new AnyValidator();
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value, $constraintsList)
    {
        $constraint = new Any(['constraints' => $constraintsList]);

        $this->validator->validate($value, $constraint);

        $this->assertNoViolation();
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value, $constraintsList)
    {
        $constraint = new Any(['constraints' => $constraintsList]);

        $this->validator->validate($value, $constraint);

        $countConstraints = count($constraintsList);
        $countViolations = count($this->context->getViolations());
        $this->assertGreaterThan($countConstraints, $countViolations);
    }

    /**
     * @return array
     */
    public static function getValidValues()
    {
        return [
            [1, [new Type(['type' => 'integer'])]],
            ['test', [new NotNull(), new Type(['type' => 'int']), new File()]]

        ];
    }

    /**
     * @return array
     */
    public static function getInvalidValues()
    {
        return [
            [1, [new Type(['type' => 'object'])]],
            ['test', [new IsNull(), new Type(['type' => 'integer']), new File()]]
        ];
    }
}
