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

namespace Diamante\DeskBundle\Tests\Model\User;

use Diamante\UserBundle\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ID = 1;

    /**
     * @var User
     */
    private $user;

    /**
     * @test
     */
    public function testUser()
    {
        $user = new User(self::TEST_ID, User::TYPE_DIAMANTE);

        $this->assertEquals(self::TEST_ID, $user->getId());
        $this->assertEquals(User::TYPE_DIAMANTE, $user->getType());
        $this->assertEquals(User::TYPE_DIAMANTE . User::DELIMITER . self::TEST_ID, (string)$user);
    }

    /**
     * @test
     */
    public function testCreatesFromString()
    {
        $user = User::fromString(User::TYPE_ORO . User::DELIMITER . self::TEST_ID);

        $this->assertEquals(User::TYPE_ORO, $user->getType());
        $this->assertEquals(self::TEST_ID, $user->getId());
    }

} 