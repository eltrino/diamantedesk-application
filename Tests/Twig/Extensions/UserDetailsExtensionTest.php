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

namespace Diamante\UserBundle\Tests\Twig\Extensions;

use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Model\UserDetails;
use Diamante\UserBundle\Twig\Extensions\UserDetailsExtension;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class UserDetailsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\UserBundle\Twig\Extensions\UserDetailsExtension
     */
    private $userDetailsExtension;

    /**
     * @var \Diamante\UserBundle\Api\Internal\UserServiceImpl
     * @Mock Diamante\UserBundle\Api\Internal\UserServiceImpl
     */
    private $userService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->userDetailsExtension = new UserDetailsExtension($this->userService);
    }

    /**
     * @test
     */
    public function testGetFunctions()
    {
        $expectedFunctions = array('fetch_user_details', 'fetch_oro_user', 'get_gravatar', 'fetch_diamante_user', 'render_user_name');

        $actualFunctions = $this->userDetailsExtension->getFunctions();

        foreach ($actualFunctions as $function)
        {
            $this->assertTrue(in_array($function->getName(), $expectedFunctions));
            $this->assertInstanceOf('\Twig_SimpleFunction', $function);
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

        $this->userService
            ->expects($this->once())
            ->method('fetchUserDetails')
            ->with($this->equalTo($user))
            ->will($this->returnValue($details));

        $actualDetails = $this->userDetailsExtension->fetchUserDetails($user);

        $this->assertInstanceOf('\Diamante\UserBundle\Model\UserDetails', $actualDetails);
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

        $this->userService
            ->expects($this->once())
            ->method('fetchUserDetails')
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
            ->method('getOroUser')
            ->with($this->equalTo($user))
            ->will($this->returnValue(new \Oro\Bundle\UserBundle\Entity\User()));

        $this->userDetailsExtension->fetchOroUser($user);
    }

    /**
     * @test
     */
    public function testGetGravatarForUser()
    {
        $details = $this->createDummyUserDetails();

        $this->userService
            ->expects($this->once())
            ->method('getGravatarLink')
            ->with($this->equalTo('email@example.com'), $this->equalTo(58))
            ->will($this->returnValue(sprintf('http://gravatar.com/avatar/%s.jpg?s=58&d=identicon', $this->getHash($details->getEmail()))));

        $link = $this->userDetailsExtension->getGravatarForUser($details->getEmail());

        $this->assertEquals(sprintf('http://gravatar.com/avatar/%s.jpg?s=58&d=identicon', $this->getHash($details->getEmail())), $link);
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