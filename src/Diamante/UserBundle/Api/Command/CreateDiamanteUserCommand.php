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

namespace Diamante\UserBundle\Api\Command;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDiamanteUserCommand
{
    /**
     * @var string
     */
    public $username;
    /**
     * @var string
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     * @Assert\Email(
     *              message="This value should be a valid email"
     * )
     */
    public $email;
    /**
     * @var string
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     */
    public $firstName;
    /**
     * @var string
     * @Assert\NotNull(
     *              message="This is a required field"
     * )
     */
    public $lastName;
}