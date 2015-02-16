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

use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\FrontBundle\Api\Command\UpdateUserCommand;

class UpdateUserService
{
    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    public function __construct(
        DiamanteUserRepository $diamanteUserRepository,
        ApiUserRepository $apiUserRepository
    ) {
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->apiUserRepository = $apiUserRepository;
    }

    /**
     * Update Diamante and Api users
     * @param UpdateUserCommand $command
     * @return void
     */
    public function update(UpdateUserCommand $command)
    {
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($command->email);
        if($diamanteUser) {
            $apiUser = $this->apiUserRepository->findUserByUsername($diamanteUser->getUsername());

            $diamanteUser->setFirstName($command->firstName)
                ->setLastName($command->lastName);
            $apiUser->setPassword($command->password);

            $this->diamanteUserRepository->store($diamanteUser);
            $this->apiUserRepository->store($apiUser);
        }
    }
}
