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

use Diamante\FrontBundle\Api\Command;
use Diamante\FrontBundle\Api\RegistrationService;
use Diamante\FrontBundle\Model\RegistrationMailer;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Infrastructure\DiamanteUserFactory;
use Diamante\UserBundle\Infrastructure\DiamanteUserRepository;
use Diamante\UserBundle\Model\ApiUser\ApiUserFactory;
use Diamante\UserBundle\Model\ApiUser\ApiUserRepository;

class RegistrationServiceImpl implements RegistrationService
{
    /**
     * @var DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var DiamanteUserFactory
     */
    private $diamanteUserFactory;

    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var ApiUserFactory
     */
    private $apiUserFactory;

    /**
     * @var RegistrationMailer
     */
    private $registrationMailer;

    public function __construct(
        DiamanteUserRepository $diamanteUserRepository, DiamanteUserFactory $diamanteUserFactory,
        ApiUserRepository $apiUserRepository, ApiUserFactory $apiUserFactory, RegistrationMailer $registrationMailer
    ) {
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->diamanteUserFactory = $diamanteUserFactory;
        $this->apiUserRepository = $apiUserRepository;
        $this->apiUserFactory = $apiUserFactory;
        $this->registrationMailer = $registrationMailer;
    }

    /**
     * Register new Diamante User and grant API access for it.
     * Sends confirmation email. While registration is not confirmed API access is not active
     * @param Command\RegisterCommand $command
     * @return void
     */
    public function register(Command\RegisterCommand $command)
    {
        $existingUser = $this->diamanteUserRepository->findUserByEmail($command->email);

        if($existingUser && !$existingUser->isDeleted()){
            throw new \RuntimeException('An account with this email address already exists');
        }

        if ($existingUser && $existingUser->isDeleted()) {
            $this->restoreUser($command, $existingUser);
            $this->registrationMailer->sendConfirmationEmail($existingUser->getEmail(), $existingUser->getApiUser()->getHash());
            return;
        }

        $diamanteUser = $this->diamanteUserFactory
            ->create($command->email, $command->firstName, $command->lastName);
        $apiUser = $this->apiUserFactory->create($command->email, $command->password);

        $diamanteUser->setApiUser($apiUser);

        $this->diamanteUserRepository->store($diamanteUser);

        $this->registrationMailer->sendConfirmationEmail($diamanteUser->getEmail(), $apiUser->getHash());
    }

    /**
     * Confirm user registration
     * @param Command\ConfirmCommand $command
     * @return void
     */
    public function confirm(Command\ConfirmCommand $command)
    {
        $apiUser = $this->apiUserRepository->findUserByHash($command->hash);

        if (is_null($apiUser)) {
            throw new \RuntimeException('Can not confirm registration.');
        }

        try {
            $apiUser->activate($command->hash);
            $this->apiUserRepository->store($apiUser);
        } catch (\Exception $e) {
            throw new \RuntimeException('Can not confirm registration.');
        }
    }

    /**
     * @param Command\RegisterCommand $command
     * @param DiamanteUser $user
     */
    protected function restoreUser(Command\RegisterCommand $command, DiamanteUser $user)
    {
        $user->setEmail($command->email);
        $user->setFirstName($command->firstName);
        $user->setLastName($command->lastName);
        $user->setDeleted(false);
        $user->getApiUser()->setPassword($command->password);

        $this->diamanteUserRepository->store($user);
    }
}
