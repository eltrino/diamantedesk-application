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


use Diamante\UserBundle\Api\Internal\UserDetailsServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class UserDetailsServiceImplTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Diamante\DeskBundle\Model\Shared\UserService
     * @Mock \Diamante\DeskBundle\Model\Shared\UserService
     */
    private $diamanteUserService;

    /**
     * @var UserDetailsServiceImpl
     */
    private $userDetailsService;


    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->userDetailsService = new UserDetailsServiceImpl($this->diamanteUserService);
    }

    /**
     * @test
     */
    public function testFetch()
    {
        $userValueObject = new User(1, User::TYPE_ORO);
        $user = new OroUser();
        $user->setId(1);
        $user->setFirstName('First');
        $user->setLastName('Last');
        $user->setEmail('email@example.com');
        $user->setUsername('username');

        $this->diamanteUserService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($userValueObject))
            ->will($this->returnValue($user));

        $userDetails = $this->userDetailsService->fetch($userValueObject);

        $this->assertEquals('oro_1', $userDetails->getId());
        $this->assertEquals(User::TYPE_ORO, $userDetails->getType());
        $this->assertEquals('First Last', $userDetails->getFullName());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Failed to load details for given user
     */
    public function testThrowsExceptionIfUserCanNotFound()
    {
        $userValueObject = new User(1, User::TYPE_ORO);

        $this->diamanteUserService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($userValueObject))
            ->will($this->returnValue(null));

        $this->userDetailsService->fetch($userValueObject);
    }
} 