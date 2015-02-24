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
use Diamante\FrontBundle\Api\Command\ConfirmCommand;
use Diamante\FrontBundle\Api\Command\RegisterCommand;
use Diamante\FrontBundle\Api\Internal\RegistrationServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class RegistrationServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegistrationServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserFactory
     * @Mock \Diamante\DeskBundle\Model\User\DiamanteUserFactory
     */
    private $diamanteUserFactory;

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

    /**
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUserFactory
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUserFactory
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
        $email = 'test@email.com';
        $password = '123123q';
        $firstname = 'Firstname';
        $lastname = 'Lastname';

        $diamanteUser = new DiamanteUser($email, $firstname, $lastname);
        $apiUser = new ApiUser($email, $password);

        $this->diamanteUserFactory->expects($this->once())->method('create')
            ->with($email, $firstname, $lastname)
            ->will($this->returnValue($diamanteUser));

        $this->apiUserFactory->expects($this->once())->method('create')
            ->with($email, $password)->will($this->returnValue($apiUser));

        $this->diamanteUserRepository->expects($this->once())->method('store')->with($diamanteUser);
        $this->apiUserRepository->expects($this->once())->method('store')->with($apiUser);

        $this->registrationMailer->expects($this->once())->method('sendConfirmationEmail')
            ->with($email, $apiUser->getHash());

        $command = new RegisterCommand();
        $command->email = $email;
        $command->password = $password;
        $command->firstname = $firstname;
        $command->lastname = $lastname;

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
        $email = 'test@email.com';
        $password = '123123q';
        $apiUser = new ApiUser($email, $password);

        $this->apiUserRepository->expects($this->once())->method('findUserByHash')
            ->with($apiUser->getHash())->will($this->returnValue($apiUser));

        $this->apiUserRepository->expects($this->once())->method('store')->with($apiUser);

        $command = new ConfirmCommand();
        $command->hash = $apiUser->getHash();

        $this->service->confirm($command);
    }
}
