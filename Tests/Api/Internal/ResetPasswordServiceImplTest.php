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
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\FrontBundle\Api\Internal\ResetPasswordServiceImpl;
use Diamante\FrontBundle\Api\Command\ResetPasswordCommand;
use Diamante\FrontBundle\Api\Command\ChangePasswordCommand;

class ResetPasswordServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ResetPasswordServiceImpl
     */
    private $resetPasswordService;

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
     * @var \Diamante\FrontBundle\Model\ResetPasswordMailer
     * @Mock \Diamante\FrontBundle\Model\ResetPasswordMailer
     */
    private $resetPasswordMailer;

    /**
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUser
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUser
     */
    private $apiUser;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->resetPasswordService = new ResetPasswordServiceImpl($this->diamanteUserRepository,
            $this->apiUserRepository,
            $this->apiUserFactory,
            $this->resetPasswordMailer,
            $this->apiUser);
    }

    public function testResetPassword()
    {
        $emailAddress = 'test@gmail.com';

        $diamanteUser = new DiamanteUser($emailAddress, 'test');
        $apiUser = new ApiUser($emailAddress, null);

        $this->diamanteUserRepository
            ->expects($this->once())
            ->method('findUserByEmail')
            ->with($this->equalTo($emailAddress))
            ->will($this->returnValue($diamanteUser));

        $this->apiUserRepository
            ->expects($this->once())
            ->method('findUserByUsername')
            ->with($this->equalTo($emailAddress))
            ->will($this->returnValue($apiUser));

        $this->apiUserRepository->expects($this->once())->method('store')->with($apiUser);

        $this->resetPasswordMailer->expects($this->once())->method('sendResetEmail')
            ->with($emailAddress, $apiUser->getActivationHash());

        $command = new ResetPasswordCommand();
        $command->email = $emailAddress;
        $this->resetPasswordService->resetPassword($command);
    }

    public function testChangePassword()
    {
        $emailAddress = 'test@example.com';
        $password = 'newPass';
        $apiUser = new ApiUser($emailAddress, null);

        $this->apiUserRepository
            ->expects($this->once())
            ->method('findUserByHash')
            ->will($this->returnValue($apiUser));

        $apiUser->generateHash();

        $hash = $apiUser->getActivationHash();

        $this->apiUserRepository
            ->expects($this->once())
            ->method('store')
            ->with($apiUser);

        $command = new ChangePasswordCommand();
        $command->password = $password;
        $command->hash = $hash;
        $this->resetPasswordService->changePassword($command);
    }

}
 