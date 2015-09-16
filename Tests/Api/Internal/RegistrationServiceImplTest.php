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

use Diamante\FrontBundle\Api\Command\ConfirmCommand;
use Diamante\FrontBundle\Api\Command\RegisterCommand;
use Diamante\FrontBundle\Api\Internal\RegistrationServiceImpl;
use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Entity\DiamanteUser;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class RegistrationServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegistrationServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\UserBundle\Infrastructure\DiamanteUserFactory
     * @Mock \Diamante\UserBundle\Infrastructure\DiamanteUserFactory
     */
    private $diamanteUserFactory;

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
     * @var \Diamante\UserBundle\Model\ApiUser\ApiUserFactory
     * @Mock \Diamante\UserBundle\Model\ApiUser\ApiUserFactory
     */
    private $apiUserFactory;

    /**
     * @var \Diamante\FrontBundle\Model\RegistrationMailer
     * @Mock \Diamante\FrontBundle\Model\RegistrationMailer
     */
    private $registrationMailer;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new RegistrationServiceImpl(
            $this->diamanteUserRepository, $this->diamanteUserFactory,
            $this->apiUserRepository, $this->apiUserFactory,
            $this->registrationMailer
        );
    }

    public function testRegister()
    {
        $apiUser = $this->createApiUser();
        $diamanteUser = $this->createDiamanteUser();

        $this->diamanteUserFactory->expects($this->once())->method('create')
            ->with($diamanteUser->getEmail(), $diamanteUser->getFirstName(), $diamanteUser->getLastName())
            ->will($this->returnValue($diamanteUser));

        $this->apiUserFactory->expects($this->once())->method('create')
            ->with($apiUser->getEmail(), $apiUser->getPassword())->will($this->returnValue($apiUser));

        $this->diamanteUserRepository->expects($this->once())->method('store')->with($diamanteUser);

        $this->registrationMailer->expects($this->once())->method('sendConfirmationEmail')
            ->with($diamanteUser->getEmail(), $apiUser->getHash());

        $command = new RegisterCommand();
        $command->email = $diamanteUser->getEmail();
        $command->password = $apiUser->getPassword();
        $command->firstName = $diamanteUser->getFirstName();
        $command->lastName = $diamanteUser->getLastName();

        $this->service->register($command);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Can not confirm registration.
     */
    public function testConfirmWhenDiamanteUserHasNoApiAccessOrInvalidHash()
    {
        $hash = md5(time());

        $this->apiUserRepository->expects($this->once())->method('findUserByHash')
            ->with($hash)->will($this->returnValue(null));

        $command = new ConfirmCommand();
        $command->hash = $hash;

        $this->service->confirm($command);
    }

    public function testConfirm()
    {
        $apiUser = $this->createApiUser();

        $this->apiUserRepository->expects($this->once())->method('findUserByHash')
            ->with($apiUser->getHash())->will($this->returnValue($apiUser));

        $this->apiUserRepository->expects($this->once())->method('store')->with($apiUser);

        $command = new ConfirmCommand();
        $command->hash = $apiUser->getHash();

        $this->service->confirm($command);
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
