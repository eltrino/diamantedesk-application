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

namespace Diamante\UserBundle\Api\Internal;

use Diamante\UserBundle\Api\UserDetailsService;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Diamante\UserBundle\Model\UserDetails;

class UserDetailsServiceImpl implements UserDetailsService
{

    public function __construct(UserService $diamanteUserService)
    {
        $this->diamanteUserService = $diamanteUserService;
    }

    /**
     * @param User $user
     * @return UserDetails|void
     */
    public function fetch(User $user)
    {
        $loadedUser = $this->diamanteUserService->getByUser($user);

        if (!$loadedUser) {
            throw new \RuntimeException('Failed to load details for given user');
        }

        return new UserDetails(
            (string)$user,
            $user->getType(),
            $loadedUser->getEmail(),
            $loadedUser->getFirstName(),
            $loadedUser->getLastName()
        );
    }


} 
