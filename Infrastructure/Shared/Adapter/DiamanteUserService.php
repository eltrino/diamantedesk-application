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
namespace Diamante\DeskBundle\Infrastructure\Shared\Adapter;

use Diamante\DeskBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\DeskBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Model\User\DiamanteUserFactory;
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
    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserFactory
     */
    private $factory;

    function __construct(
        UserManager $userManager,
        DiamanteUserRepository $diamanteUserRepository,
        DiamanteUserFactory $factory
    )
    {
        $this->oroUserManager         = $userManager;
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->factory                = $factory;
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

    /**
     * @param string $email
     * @return int|null
     */
    public function verifyDiamanteUserExists($email)
    {
        $user = $this->diamanteUserRepository->findUserByEmail($email);

        if (empty($user)) {
            return null;
        }

        return $user->getId();
    }

    /**
     * @param \Diamante\DeskBundle\Api\Command\CreateDiamanteUserCommand $command
     * @return int
     */
    public function createDiamanteUser(CreateDiamanteUserCommand $command)
    {
        $user = $this->factory->create(
            $command->username,
            $command->email,
            $command->contact,
            $command->firstName,
            $command->lastName
        );

        $this->diamanteUserRepository->store($user);

        return $user->getId();
    }
}