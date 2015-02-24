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
namespace Diamante\FrontBundle\Api;

interface RegistrationService
{
    /**
     * Register new Diamante User and grant API access for it.
     * Sends confirmation email. While registration is not confirmed API access is not active
     * @param Command\RegisterCommand $command
     * @return void
     */
    public function register(Command\RegisterCommand $command);

    /**
     * Confirm user registration
     * @param Command\ConfirmCommand $command
     * @return void
     */
    public function confirm(Command\ConfirmCommand $command);
}
