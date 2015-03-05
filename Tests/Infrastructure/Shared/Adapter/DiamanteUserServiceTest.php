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
namespace Diamante\DeskBundle\Tests\Infrastructure\Shared\Adapter;

use Diamante\DeskBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Infrastructure\Shared\Adapter\DiamanteUserService;
use Diamante\DeskBundle\Model\User\DiamanteUserFactory;
use Diamante\DeskBundle\Model\User\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class DiamanteUserServiceTest extends \PHPUnit_Framework_TestCase
{

    const DUMMY_EMAIL = 'test@gmail.com';

    /**
     * @var \Oro\Bundle\UserBundle\Entity\UserManager
     * @Mock \Oro\Bundle\UserBundle\Entity\UserManager
     */
    private $oroUserManager;

    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     * @Mock \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var DiamanteUserService
     */
    private $diamanteUserService;

    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserFactory
     * @Mock Diamante\DeskBundle\Model\User\DiamanteUserFactory
     */
    private $diamanteUserFactory;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->diamanteUserService = new DiamanteUserService(
            $this->oroUserManager,
            $this->diamanteUserRepository,
            $this->diamanteUserFactory
        );
    }

    /**
     * @test
     */
    public function testGetOROTypeUserByUser()
    {
        $userValueObject = new User(1, User::TYPE_ORO);
        $user = new OroUser();

        $this->oroUserManager
            ->expects($this->once())
            ->method('findUserBy')
            ->with($this->equalTo(array('id' => $userValueObject->getId())))
            ->will($this->returnValue($user));

        $this->diamanteUserService->getByUser($userValueObject);
    }

    /**
     * @test
     */
    public function testGetDiamanteTypeUserByUser()
    {
        $userValueObject = new User(1, User::TYPE_DIAMANTE);
        $user = new DiamanteUser(self::DUMMY_EMAIL);

        $this->diamanteUserRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($userValueObject->getId()))
            ->will($this->returnValue($user));

        $this->diamanteUserService->getByUser($userValueObject);
    }


    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User loading failed. User not found
     */
    public function testThrowsExceptionIfUserNotFound()
    {
        $userValueObject = new User(1, User::TYPE_DIAMANTE);

        $this->diamanteUserRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($userValueObject->getId()))
            ->will($this->returnValue(null));

        $this->diamanteUserService->getByUser($userValueObject);
    }

} 
