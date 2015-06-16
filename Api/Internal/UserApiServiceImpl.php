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
use Diamante\UserBundle\Api\Command\CreateDiamanteUserCommand;
use Diamante\UserBundle\Entity\DiamanteUser;

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
}