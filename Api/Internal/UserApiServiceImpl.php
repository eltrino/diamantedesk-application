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

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\ApiBundle\Routing\RestServiceInterface;
use Diamante\DeskBundle\Api\Internal\ApiServiceImplTrait;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Model\User;

class UserApiServiceImpl extends UserServiceImpl implements RestServiceInterface
{

    use ApiServiceImplTrait;

    /**
     * Create Diamante User
     *
     * @ApiDoc(
     *  description="Create DiamanteUser",
     *  uri="/users.{_format}",
     *  method="POST",
     *  resource=true,
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to create branch"
     *  }
     * )
     *
     * @param CreateDiamanteUserCommand $command
     * @return DiamanteUser
     */
    public function createDiamanteUser(CreateDiamanteUserCommand $command)
    {
        $userId = parent::createDiamanteUser($command);
        return $this->diamanteUserRepository->get($userId);
    }

    /**
     * Retrieves DiamanteUser data if one exists
     *
     * @ApiDoc(
     *  description="Returns person data",
     *  uri="/users/{email}/.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *       {
     *           "name"="email",
     *           "dataType"="string",
     *           "description"="Email address"
     *       }
     *   },
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when user not found",
     *      403="Returned when the user is not authorized to view diamante users"
     *  }
     * )
     *
     * @param $email
     * @return DiamanteUser
     */
    public function getUser($email)
    {
        $userId = parent::verifyDiamanteUserExists($email);
        if (!$userId) {
            throw new EntityNotFoundException('User not found.');
        }

        return parent::getDiamanteUser(new User($userId, User::TYPE_ORO));
    }
}