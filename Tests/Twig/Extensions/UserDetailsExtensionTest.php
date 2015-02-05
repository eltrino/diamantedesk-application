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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 4/12/14
 * Time: 4:13 PM
 */

namespace Diamante\DeskBundle\Tests\Twig\Extensions;


use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Model\User\UserDetails;
use Diamante\DeskBundle\Twig\Extensions\UserDetailsExtension;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class UserDetailsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\DeskBundle\Model\User\UserDetailsService
     * @Mock Diamante\DeskBundle\Model\User\UserDetailsService
     */
    private $userDetailsService;

    /**
     * @var \Diamante\DeskBundle\Twig\Extensions\UserDetailsExtension
     */
    private $userDetailsExtension;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\UserService
     * @Mock Diamante\DeskBundle\Model\Shared\UserService
     */
    private $userService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->userDetailsExtension = new UserDetailsExtension($this->userDetailsService, $this->userService);
    }

    /**
     * @test
     */
    public function testGetFunctions()
    {
        $expectedFunctions = array('fetch_user_details', 'fetch_oro_user', 'get_gravatar');

        $actualFunctions = $this->userDetailsExtension->getFunctions();

        foreach ($expectedFunctions as $function)
        {
            $this->assertTrue(array_key_exists($function, $actualFunctions));
            $this->assertInstanceOf('\Twig_Function_Method', $actualFunctions[$function]);
        }
    }

    /**
     * @test
     */
    public function testFetchUserDetails()
    {
        $id = 'diamante_1';
        $user = User::fromString($id);
        $details = $this->createDummyUserDetails();

        $this->userDetailsService
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($user))
            ->will($this->returnValue($details));

        $actualDetails = $this->userDetailsExtension->fetchUserDetails($user);

        $this->assertInstanceOf('\Diamante\DeskBundle\Model\User\UserDetails', $actualDetails);
        $this->assertNotNull($actualDetails->getId());
        $this->assertNotNull($actualDetails->getType());
        $this->assertEquals(User::TYPE_DIAMANTE, $actualDetails->getType());
        $this->assertEquals((string)$user, $actualDetails->getId());
    }

    /**
     * @test
     * @expectedException \Twig_Error_Runtime
     * @expectedExceptionMessage Failed to load user details
     */
    public function testThatExceptionIsThrownIfNoUserDetailsExist()
    {
        $id = 'diamante_1';
        $user = User::fromString($id);

        $this->userDetailsService
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($user))
            ->will($this->returnValue(null));

        $this->userDetailsExtension->fetchUserDetails($user);
    }

    public function testFetchOroUser()
    {
        $id = 'oro_1';
        $user = User::fromString($id);

        $this->userService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($user))
            ->will($this->returnValue(new \Oro\Bundle\UserBundle\Entity\User()));

        $this->userDetailsExtension->fetchOroUser($user);
    }

    /**
     * @test
     * @expectedException \Twig_Error_Runtime
     * @expectedExceptionMessage Failed to load user
     */
    public function testExceptionIsThrownIfNoOroUserExists()
    {
        $id = 'oro_1';
        $user = User::fromString($id);

        $this->userService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($user))
            ->will($this->returnValue(null));

        $this->userDetailsExtension->fetchOroUser($user);
    }

    /**
     * @test
     */
    public function testGetGravatarForUser()
    {
        $user    = User::fromString('diamante_1');
        $details = $this->createDummyUserDetails();

        $this->userDetailsService
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($user))
            ->will($this->returnValue($details));

        $link = $this->userDetailsExtension->getGravatarForUser($user);

        $this->assertEquals(sprintf('http://gravatar.com/avatar/%s.jpg?s=58&d=identicon', $this->getHash($details->getEmail())), $link);
    }

    /**
     * @test
     * @expectedException \Twig_Error_Runtime
     * @expectedExceptionMessage Invalid user details source is provided. Expected instance of Diamante\DeskBundle\Model\User\User or Diamante\DeskBundle\Model\User\UserDetails, ArrayObject given
     */
    public function testGetGravatarForUserThrowsExceptionOnInvalidDataSourceType()
    {
        $this->userDetailsExtension->getGravatarForUser(new \ArrayObject());
    }

    /**
     * @return UserDetails
     */
    protected function createDummyUserDetails()
    {
        return new UserDetails(User::TYPE_DIAMANTE . User::DELIMITER . 1, User::TYPE_DIAMANTE, 'email@example.com', 'First', 'Last','username');
    }

    /**
     * @param string $email
     * @return string
     */
    private function getHash($email)
    {
        return md5(strtolower(trim($email)));
    }
} 