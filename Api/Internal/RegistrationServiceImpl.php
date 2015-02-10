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

use Diamante\ApiBundle\Model\ApiUser\ApiUserFactory;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\DeskBundle\Model\User\DiamanteUser;
use Diamante\DeskBundle\Model\User\DiamanteUserFactory;
use Diamante\DeskBundle\Model\User\DiamanteUserRepository;
use Diamante\FrontBundle\Api\Command;
use Diamante\FrontBundle\Api\RegistrationService;
use Diamante\FrontBundle\Model\RegistrationMailer;

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
        $diamanteUser = $this->diamanteUserFactory
            ->create($command->email, $command->username, $command->firstname, $command->lastname);
        $apiUser = $this->apiUserFactory->create($command->username, $command->password);

        $this->diamanteUserRepository->store($diamanteUser);
        $this->apiUserRepository->store($apiUser);

        $this->registrationMailer->sendConfirmationEmail($diamanteUser->getEmail(), $apiUser->getActivationHash());
    }
}
