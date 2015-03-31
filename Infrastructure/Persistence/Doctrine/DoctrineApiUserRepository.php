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
namespace Diamante\UserBundle\Infrastructure\Persistence\Doctrine;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Model\ApiUser\ApiUserRepository;
use Doctrine\ORM\Query;

class DoctrineApiUserRepository extends DoctrineGenericRepository implements ApiUserRepository
{
    /**
     * Finds a user by email
     *
     * @param  string $email
     * @return ApiUser
     */
    public function findUserByEmail($email)
    {
        return $this->findOneBy(array('email' => $email));
    }

    /**
     * Finds a user by hash
     *
     * @param  string $hash
     * @return ApiUser
     */
    public function findUserByHash($hash)
    {
        return $this->findOneBy(array('hash' => $hash));
    }
}
