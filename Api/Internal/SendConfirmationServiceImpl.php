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

use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\FrontBundle\Api\Command\SendConfirmCommand;
use Diamante\FrontBundle\Api\SendConfirmationService;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineApiUserRepository;
use Diamante\FrontBundle\Infrastructure\RegistrationMailer;

class SendConfirmationServiceImpl implements SendConfirmationService
{

    /**
     * @var DoctrineApiUserRepository
     */
    protected $apiUserRepository;


    /**
     * @var RegistrationMailer
     */
    protected $registrationMailer;

    public function __construct(
        DoctrineApiUserRepository $apiUserRepository,
        RegistrationMailer $registrationMailer
    ) {
        $this->apiUserRepository = $apiUserRepository;
        $this->registrationMailer = $registrationMailer;
    }

    /**
     * Send user confirmation email
     * @param SendConfirmCommand $command
     * @return void
     */
    public function send(SendConfirmCommand $command)
    {
        $apiUser = $this->apiUserRepository->findUserByEmail($command->email);
        if (!$apiUser) {
            throw new EntityNotFoundException;
        }

        if ($apiUser->isActive()) {
            throw new \RuntimeException('User is already activated');
        }

        $apiUser->generateHash();
        $this->apiUserRepository->store($apiUser);
        $this->registrationMailer->sendConfirmationEmail($command->email, $apiUser->getHash());
    }

}