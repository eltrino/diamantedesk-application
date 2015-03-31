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
namespace Diamante\UserBundle\Tests\Model\ApiUser;


use Diamante\UserBundle\Entity\ApiUser;

class ApiUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Given hash is invalid and user can not be activated.
     */
    public function testActivateWhenHashIsInavalid()
    {
        $apiUser = new ApiUser('username', 'password');
        $apiUser->activate(md5('dummy' . time()));
    }

    public function testActivate()
    {
        $apiUser = new ApiUser('username', 'password');
        $hash = $apiUser->getHash();
        $apiUser->activate($hash);

        $this->assertTrue($apiUser->isActive());
    }

    public function testGenerateHash()
    {
        $apiUser = new ApiUser('username', 'password');
        $apiUser->generateHash();
    }

    public function testChangePassword()
    {
        $newPassword = '123123';
        $apiUser = new ApiUser('username', 'password');
        $apiUser->generateHash();
        $apiUser->changePassword($newPassword);

        $this->assertTrue($apiUser->isActive());
        $this->assertEquals($apiUser->getPassword(), $newPassword);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Given hash is invalid and password can not be reset.
     */
    public function testChangePasswordWhenTimeIsExpired()
    {
        $newPassword = '123123';
        $apiUser = new ApiUser('username', 'password');
        $apiUser->changePassword($newPassword);
    }
}
