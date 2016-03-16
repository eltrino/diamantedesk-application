<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\UserBundle\DataFixtures\Test;

use Diamante\UserBundle\Api\Internal\UserServiceImpl;
use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Entity\DiamanteUser;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDiamanteUsersData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {

        for ($i = 1; $i <= 10; $i ++) {
            $email = sprintf("test%d@example.com", $i);
            $user = new DiamanteUser($email, 'Test', 'User');
            $apiUser = new ApiUser($email, UserServiceImpl::generateRandomSequence(20), UserServiceImpl::generateRandomSequence(20));
            $apiUser->activate($apiUser->getHash());
            $apiUser->setDiamanteUser($user);
            $user->setApiUser($apiUser);
            $user->setDeleted(false);

            $manager->persist($user);
        }

        $manager->flush();
    }
}