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
use Diamante\ApiBundle\Model\ApiUser\ApiUser;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\User\DiamanteUser;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\FrontBundle\Api\Command\UpdateUserCommand;
use Diamante\ApiBundle\Routing\RestServiceInterface;
use Diamante\FrontBundle\Api\CurrentUserService;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Exception\AuthenticationException;


class CurrentUserApiServiceImpl implements CurrentUserService, RestServiceInterface
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
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        DiamanteUserRepository $diamanteUserRepository,
        ApiUserRepository $apiUserRepository,
        AuthorizationService $authorizationService,
        Logger $logger
    ) {
        $this->diamanteUserRepository   = $diamanteUserRepository;
        $this->apiUserRepository        = $apiUserRepository;
        $this->authorizationService     = $authorizationService;
        $this->logger                   = $logger;
    }

    /**
     * Returns current user from session
     *
     * @ApiDoc(
     *  description="Get current user",
     *  uri="/users/current.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      401="Returned when the user is not found"
     *  }
     * )
     * @return \Diamante\DeskBundle\Model\User\DiamanteUser
     */
    public function getCurrentUser()
    {
        $apiUser = $this->authorizationService->getLoggedUser();

        if (!$apiUser instanceof ApiUser) {
            throw new ForbiddenException('Your session seems to be dirty. Please, log out of Diamante Admin and try again');
        }

        try {
            $diamanteUser = $this->loadDiamanteUser($apiUser);
            return $diamanteUser;
        } catch (\Exception $e) {
            $this->logger->error('No Diamante User is present for ApiUser provided');
            throw new AuthenticationException('Attempt of unauthorized access');
        }
    }


    /**
     * Update Diamante and Api users related to current session
     *
     * @ApiDoc(
     *  description="Update current user",
     *  uri="/users/current.{_format}",
     *  method={
     *      "PATCH",
     *      "PUT"
     *  },
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update user",
     *      404="Returned when the user is not found"
     *  }
     * )
     *
     * @param UpdateUserCommand $command
     * @return DiamanteUser
     */
    public function update(UpdateUserCommand $command)
    {
        $apiUser = $this->authorizationService->getLoggedUser();

        $diamanteUser = $this->loadDiamanteUser($apiUser);

        if ($command->firstName) {
            $diamanteUser->setFirstName($command->firstName);
        }

        if ($command->lastName) {
            $diamanteUser->setLastName($command->lastName);
        }

        if ($command->password) {
            $apiUser->setPassword($command->password);
        }

        $this->diamanteUserRepository->store($diamanteUser);
        $this->apiUserRepository->store($apiUser);

        return $diamanteUser;
    }

    /**
     * @param ApiUser $apiUser
     * @return DiamanteUser
     */
    private function loadDiamanteUser(ApiUser $apiUser)
    {
        $diamanteUser = $this->diamanteUserRepository->findUserByEmail($apiUser->getEmail());
        if (is_null($diamanteUser)) {
            throw new \RuntimeException('User loading failed, user not found.');
        }
        return $diamanteUser;
    }
}
