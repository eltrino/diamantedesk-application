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
namespace Diamante\ApiBundle\Model\ApiUser;

use Diamante\DeskBundle\Model\Shared\Entity;
use \OroCRM\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiUser implements Entity, UserInterface
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var array
     */
    protected $roles = array();

    /**
     * @var string
     */
    protected $salt;

    public function __construct($username, $password, $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt     = $salt;
        $this->roles    = $roles;
    }

    /**
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return array|\Symfony\Component\Security\Core\Role\Role[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {

    }
}