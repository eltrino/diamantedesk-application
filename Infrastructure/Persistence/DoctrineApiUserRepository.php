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
namespace Diamante\ApiBundle\Infrastructure\Persistence;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\ApiBundle\Model\ApiUser\ApiUser;
use Doctrine\ORM\Query;

class DoctrineApiUserRepository extends DoctrineGenericRepository implements ApiUserRepository
{
    /**
     * Finds a user by username
     *
     * @param  string $username
     * @return ApiUser
     */
    public function findUserByUsername($username)
    {
        return $this->findOneBy(array('username' => $username));
    }
}
