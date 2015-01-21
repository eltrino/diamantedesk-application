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

use Diamante\DeskBundle\Validator\Constraints\User;
use Diamante\DeskBundle\Validator\Constraints\UserValidator;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class UserValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Validator\ExecutionContext
     * @Mock \Symfony\Component\Validator\ExecutionContext
     */
    private $context;

    /** @var UserValidator */
    private $validator;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->validator = new UserValidator();
        $this->validator->initialize($this->context);
    }

    /**
     * @dataProvider getValidValues
     */
    public function testValidValues($value)
    {
        $this->context
            ->expects($this->never())
            ->method('addViolation');

        $constraint = new User();
        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider getInvalidValues
     */
    public function testInvalidValues($value)
    {
        $this->context
            ->expects($this->once())
            ->method('addViolation');

        $constraint = new User();
        $this->validator->validate($value, $constraint);
    }

    public static function getValidValues()
    {
        return [
            ['oro_1'],
            ['diamante_1'],
            [new \Diamante\DeskBundle\Model\User\User(1, 'oro')],
            [new \Diamante\DeskBundle\Model\User\User(1, 'diamante')]
        ];
    }

    public static function getInvalidValues()
    {
        return [
            ['roro_1'],
            ['qdiamante_1'],
            ['qdiamante_1a'],
            ['qdiamante_1a'],
            [new \stdClass()]
        ];
    }
}
