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

use Diamante\ApiBundle\Entity\ApiUser;
use Diamante\FrontBundle\Api\ResetPassword;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\ApiBundle\Infrastructure\Persistence\DoctrineApiUserRepository;

class ResetPasswordService implements ResetPassword
{

    const EXPIRE_TIME = 900;//Hash expiration time in seconds (15 minutes);

    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;


    /**
     * @param DiamanteUserRepository $diamanteUserRepository
     */
    public function __construct(DiamanteUserRepository $diamanteUserRepository,
                                DoctrineApiUserRepository $apiUserRepository)
    {
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->apiUserRepository = $apiUserRepository;
    }

    /**
     * @param $emailAddress
     */
    public function generateHash($emailAddress)
    {

        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($emailAddress);
        if (is_null($diamanteUser)) {
            throw new \RuntimeException('No accounts with that email found.');
        }

        $apiUser = $this->apiUserRepository->findUserByUsername($emailAddress);
        if (is_null($apiUser)) {
            $apiUser = new ApiUser($diamanteUser->getEmail(), null);
        }
        $timestamp = time();
        $hash = md5($apiUser->getUsername(), $timestamp, $apiUser->getPassword());
        $apiUser->setHash($hash);
        $apiUser->setHashExpireTime($timestamp + self::EXPIRE_TIME);

        //$this->apiUserRepository->store($apiUser);



//        -generates and saves hash
//        -sends email

    }


    public function checkHash($hash, $newPassword)
    {
        $apiUser = $this->apiUserRepository->findUserByHash($hash);

        if (is_null($apiUser)) {
            throw new \RuntimeException('This password reset code is invalid.');
        }

        if (time() > $apiUser->getHashExpireTime())
        {
            throw new \RuntimeException('This password reset code is invalid.');
        }


        $apiUser->setPassword($newPassword);
        $apiUser->setActive(true);

        $this->apiUserRepository->store($apiUser);

    }

} 