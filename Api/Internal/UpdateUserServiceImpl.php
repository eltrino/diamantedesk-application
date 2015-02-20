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

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\FrontBundle\Api\Command\UpdateUserCommand;
use Diamante\ApiBundle\Routing\RestServiceInterface;
use Diamante\FrontBundle\Api\UpdateUserService;

class UpdateUserServiceImpl implements UpdateUserService, RestServiceInterface
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
     *
     * @ApiDoc(
     *  description="Update user",
     *  uri="/users/{id}.{_format}",
     *  method={
     *      "PATCH",
     *      "POST"
     *  },
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="User Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update user",
     *      404="Returned when the user is not found"
     *  }
     * )
     *
     * @param UpdateUserCommand $command
     * @return void
     */
    public function update(UpdateUserCommand $command)
    {
        $diamanteUser = $this->loadUserBy($command->id);
        $apiUser = $this->apiUserRepository->findUserByUsername($diamanteUser->getUsername());

        $diamanteUser->setFirstName($command->firstName)
            ->setLastName($command->lastName);
        $apiUser->setPassword($command->password);

        $this->diamanteUserRepository->store($diamanteUser);
        $this->apiUserRepository->store($apiUser);
    }

    /**
     * @param $userId
     * @return \Diamante\DeskBundle\Entity\DiamanteUser
     */
    private function loadUserBy($userId)
    {
        $user = $this->diamanteUserRepository->get($userId);
        if (is_null($user)) {
            throw new \RuntimeException('User loading failed, user not found.');
        }
        return $user;
    }
}
