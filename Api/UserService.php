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
namespace Diamante\UserBundle\Api;

use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Model\ApiUser\ApiUser;
use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Model\UserDetails;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

/**
 * Interface UserService
 * @package Diamante\DeskBundle\Model\Shared
 * @codeCoverageIgnore
 */
interface UserService
{
    /**
     * Retrieves User|DiamanteUser entity
     * @param User $user
     * @return OroUser|DiamanteUser
     */
    public function getByUser(User $user);

    /**
     * Retrieve User details as object
     * @param \Diamante\UserBundle\Model\User $user
     * @return UserDetails
     */
    public function fetchUserDetails(User $user);

    /**
     * Retrieve Oro User object
     * @param \Diamante\UserBundle\Model\User $user
     * @return bool|OroUser
     */
    public function getOroUser(User $user);

    /**
     * Retrieve Diamante User object
     * @param \Diamante\UserBundle\Model\User $user
     * @return bool|DiamanteUser
     */
    public function getDiamanteUser(User $user);

    /**
     * @param string $email
     * @return int|null
     */
    public function verifyDiamanteUserExists($email);

    /**
     * @param ApiUser $user
     * @return DiamanteUser|null
     */
    public function getUserFromApiUser(ApiUser $user);

    /**
     * @param CreateDiamanteUserCommand $command
     * @return DiamanteUser
     */
    public function createDiamanteUser(CreateDiamanteUserCommand $command);

    /**
     * @param $email
     * @return User
     */
    public function getUserByEmail($email);
}
