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
namespace Eltrino\DiamanteDeskBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\UserManager;

class OroUserWrapper implements UserWrapper
{
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Find User by id
     * @param integer $id
     * @return User
     */
    public function findById($id)
    {
        $oroUser = $this->userManager->findUserBy(array('id' => $id));
        if (!$oroUser->getId()) {
            throw new \LogicException("User doesn't exist.");
        }
        return new User(
            $oroUser->getId(),
            $oroUser->getUsername(),
            $oroUser->getFirstName() . ' ' . $oroUser->getLastName()
        );
    }

    /**
     * Find all Users
     * @return ArrayCollectionn of User
     */
    public function findAll()
    {
        $users = new ArrayCollection();
        $oroUsers = $this->userManager->findUsers();
        foreach ($oroUsers as $oroUser) {
            $users->add(
                new User(
                    $oroUser->getId(),
                    $oroUser->getUsername(),
                    $oroUser->getFirstName() . ' ' . $oroUser->getLastName()
                )
            );
        }
        return $users;
    }
}
