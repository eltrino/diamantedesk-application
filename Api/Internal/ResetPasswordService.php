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

use Diamante\FrontBundle\Api\ResetPassword;
use Diamante\DeskBundle\Model\User;

class ResetPasswordService implements ResetPassword
{

    /**
     * @param User $user
     * @return mixed
     */
    public function grantApiAccess(User $user)
    {

    }

    /**
     * @param User $user
     * @return mixed
     */
    public function generateHash(User $user)
    {

    }

    /**
     * @param User $user
     * @return mixed
     */
    public function sendEmail(User $user)
    {

    }

} 