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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 19/11/14
 * Time: 8:03 PM
 */

namespace Diamante\DeskBundle\Infrastructure\Shared\Adapter;

use Diamante\DeskBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\DeskBundle\Model\Shared\UserService;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Oro\Bundle\UserBundle\Entity\User;
use Diamante\DeskBundle\Model\User\User as UserAdapter;

class DiamanteUserService implements UserService
{
    /**
    * @var UserManager
    */
    private $oroUserManager;

    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    function __construct(
        UserManager $userManager,
        DiamanteUserRepository $diamanteUserRepository
    )
    {
        $this->oroUserManager         = $userManager;
        $this->diamanteUserRepository = $diamanteUserRepository;
    }

    /**
     * @param UserAdapter $user
     * @return DiamanteUser|User
     */
    public function getByUser(UserAdapter $user)
    {
        if ($user->isOroUser()) {
            $user = $this->oroUserManager->findUserBy(array('id' => $user->getId()));
        } else {
            $user = $this->diamanteUserRepository->get($user->getId());
        }

        if (!$user) {
            throw new \RuntimeException('User loading failed. User not found');
        }

        return $user;
    }
}