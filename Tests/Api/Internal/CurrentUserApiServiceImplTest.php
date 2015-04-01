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
namespace Diamante\FrontBundle\Tests\Api\Internal;

use Diamante\FrontBundle\Api\Command\UpdateUserCommand;
use Diamante\FrontBundle\Api\Internal\CurrentUserApiServiceImpl;
use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Entity\DiamanteUser;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class CurrentUserApiServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CurrentUserApiServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\UserBundle\Infrastructure\DiamanteUserRepository
     * @Mock \Diamante\UserBundle\Infrastructure\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var \Diamante\UserBundle\Model\ApiUser\ApiUserRepository
     * @Mock \Diamante\UserBundle\Model\ApiUser\ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService
     * @Mock \Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService
     */
    private $authorizationService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new CurrentUserApiServiceImpl(
            $this->diamanteUserRepository,
            $this->apiUserRepository,
            $this->authorizationService
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User loading failed, user not found.
     */
    public function testUpdateWhenDiamanteUserDoesNotExist()
    {
        $apiUser = $this->createApiUser();
        $firstName = 'Firstname';
        $lastName = 'Lastname';

        $this->authorizationService->expects($this->once())->method('getLoggedUser')
            ->will($this->returnValue($apiUser));
        $this->diamanteUserRepository->expects($this->once())->method('findUserByEmail')
            ->with($apiUser->getEmail())->will($this->returnValue(null));

        $command = new UpdateUserCommand();
        $command->password = $apiUser->getPassword();
        $command->firstName = $firstName;
        $command->lastName = $lastName;

        $this->service->update($command);
    }

    public function testUpdate()
    {
        $diamanteUser = $this->createDiamanteUser();
        $apiUser = $this->createApiUser();

        $this->authorizationService->expects($this->once())->method('getLoggedUser')
            ->will($this->returnValue($apiUser));
        $this->diamanteUserRepository->expects($this->once())->method('findUserByEmail')
            ->with($apiUser->getEmail())->will($this->returnValue($diamanteUser));

        $command = new UpdateUserCommand();
        $command->password = "mod_" . $apiUser->getPassword();
        $command->firstName = "mod_" . $diamanteUser->getFirstName();
        $command->lastName = "mod_" . $diamanteUser->getLastName();

        $this->service->update($command);

        $this->assertStringStartsWith("mod_", $diamanteUser->getFirstName());
        $this->assertStringStartsWith("mod_", $diamanteUser->getLastName());
        $this->assertStringStartsWith("mod_", $apiUser->getPassword());
    }

    public function testGetCurrentUser()
    {
        $diamanteUser = $this->createDiamanteUser();
        $apiUser = $this->createApiUser();

        $this->authorizationService->expects($this->once())->method('getLoggedUser')
            ->will($this->returnValue($apiUser));
        $this->diamanteUserRepository->expects($this->once())->method('findUserByEmail')
            ->with($apiUser->getEmail())->will($this->returnValue($diamanteUser));

        $this->assertEquals($diamanteUser, $this->service->getCurrentUser());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User loading failed, user not found.
     */
    public function testGetCurrentUserWithNoDiamanteUser()
    {
        $apiUser = $this->createApiUser();

        $this->authorizationService->expects($this->once())->method('getLoggedUser')
            ->will($this->returnValue($apiUser));
        $this->diamanteUserRepository->expects($this->once())->method('findUserByEmail')
            ->with($apiUser->getEmail())->will($this->returnValue(null));

        $this->service->getCurrentUser();
    }

    /**
     * @return ApiUser
     */
    private function createApiUser()
    {
        $apiUser = new ApiUser('test@email.com', '3F8117C1CEC19534C385EE9EC1E8713E884F6F7C');
        return $apiUser;
    }

    /**
     * @return DiamanteUser
     */
    private function createDiamanteUser()
    {
        $email = 'test@email.com';
        $firstName = 'Firstname';
        $lastName = 'Lastname';

        $diamanteUser = new DiamanteUser($email, null, $firstName, $lastName);
        return $diamanteUser;
    }
}
