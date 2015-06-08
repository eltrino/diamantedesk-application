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

namespace Diamante\FrontBundle\Api\Internal;

use Diamante\FrontBundle\Model\ResetPasswordMailer;
use Diamante\FrontBundle\Api\ResetPasswordService;
use Diamante\FrontBundle\Api\Command\ResetPasswordCommand;
use Diamante\FrontBundle\Api\Command\ChangePasswordCommand;
use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\ApiUser\ApiUserFactory;
use Diamante\UserBundle\Model\ApiUser\ApiUserRepository;

class ResetPasswordServiceImpl implements ResetPasswordService
{

    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var ApiUserFactory
     */
    private $apiUserFactory;

    /**
     * @var ResetPasswordMailer
     */
    private $resetPasswordMailer;


    /**
     * @param DiamanteUserRepository $diamanteUserRepository
     * @param ApiUserRepository $apiUserRepository
     * @param ApiUserFactory $apiUserFactory
     * @param ResetPasswordMailer $resetPasswordMailer
     */
    public function __construct(DiamanteUserRepository $diamanteUserRepository,
                                ApiUserRepository $apiUserRepository,
                                ApiUserFactory $apiUserFactory,
                                ResetPasswordMailer $resetPasswordMailer)
    {
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->apiUserRepository = $apiUserRepository;
        $this->apiUserFactory = $apiUserFactory;
        $this->resetPasswordMailer = $resetPasswordMailer;
    }

    /**
     * @param ResetPasswordCommand $command
     * @return void
     * @throws \RuntimeException if given emailAddres is not equal to generated one for user
     */
    public function resetPassword(ResetPasswordCommand $command)
    {
        /**
         * @var DiamanteUser $diamanteUser
         */
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($command->email);
        if (is_null($diamanteUser)) {
            throw new \RuntimeException('No accounts with that email found.');
        }

        /**
         * @var ApiUser $apiUser
         */
        $apiUser = $this->apiUserRepository->findUserByEmail($command->email);
        if (is_null($apiUser)) {
            $apiUser = $this->apiUserFactory->create($command->email, sha1(microtime(true), true));
        }

        $apiUser->generateHash();

        $this->apiUserRepository->store($apiUser);

        $this->resetPasswordMailer->sendResetEmail($diamanteUser->getEmail(), $apiUser->getHash());

    }

    /**
     * @param ChangePasswordCommand $command
     * @return void
     */
    public function changePassword(ChangePasswordCommand $command)
    {
        /**
         * @var ApiUser $apiUser
         */
        $apiUser = $this->apiUserRepository->findUserByHash($command->hash);

        if (is_null($apiUser)) {
            throw new \RuntimeException('Your password reset link has expired.');
        }

        $apiUser->changePassword($command->password);

        $this->apiUserRepository->store($apiUser);
    }

} 
