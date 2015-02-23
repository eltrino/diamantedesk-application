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

use Diamante\ApiBundle\Model\ApiUser\ApiUser;
use Diamante\DeskBundle\Model\User\DiamanteUser;
use Diamante\FrontBundle\Api\Command\UpdateUserCommand;
use Diamante\FrontBundle\Api\Internal\UpdateUserServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class UpdateUserServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateUserServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     * @Mock \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUserRepository
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUserRepository
     */
    private $apiUserRepository;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new UpdateUserServiceImpl(
            $this->diamanteUserRepository,
            $this->apiUserRepository
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User loading failed, user not found.
     */
    public function testUpdateWhenDiamanteUserDoesNotExist()
    {
        $id = 1;
        $password = '123123q';
        $firstname = 'Firstname';
        $lastname = 'Lastname';

        $this->diamanteUserRepository->expects($this->once())->method('get')
            ->with($id)->will($this->returnValue(null));

        $command = new UpdateUserCommand();
        $command->id = $id;
        $command->password = $password;
        $command->firstName = $firstname;
        $command->lastName = $lastname;

        $this->service->update($command);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Diamante User is not granted for API Access.
     */
    public function testUpdateWhenDiamanteUserHasNoApiAccess()
    {
        $id = 1;
        $email = 'test@email.com';
        $username = 'testuser';
        $firstname = 'Firstname';
        $password = '123123q';
        $lastname = 'Lastname';
        
        $diamanteUser = new DiamanteUser($email, $username, $firstname, $lastname);

        $this->diamanteUserRepository->expects($this->once())->method('get')
            ->with($id)->will($this->returnValue($diamanteUser));

        $this->apiUserRepository->expects($this->once())->method('findUserByUsername')
            ->with($diamanteUser->getUsername())->will($this->returnValue(null));

        $command = new UpdateUserCommand();
        $command->id = $id;
        $command->password = $password;
        $command->firstName = $firstname;
        $command->lastName = $lastname;

        $this->service->update($command);
    }

    public function testUpdate()
    {
        $id = 1;
        $email = 'test@email.com';
        $username = 'testuser';
        $password = '123123q';
        $firstname = 'Firstname';
        $lastname = 'Lastname';

        $diamanteUser = new DiamanteUser($email, $username, $firstname, $lastname);
        $apiUser = new ApiUser($username, $password);

        $this->diamanteUserRepository->expects($this->once())->method('get')
            ->with($id)->will($this->returnValue($diamanteUser));
        $this->apiUserRepository->expects($this->once())->method('findUserByUsername')
            ->with($username)->will($this->returnValue($apiUser));

        $command = new UpdateUserCommand();
        $command->id = $id;
        $command->password = $password;
        $command->firstName = $firstname;
        $command->lastName = $lastname;

        $this->service->update($command);
    }
}
