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
namespace Diamante\DeskBundle\Model\User;

use Diamante\DeskBundle\Model\Shared\Entity;
use \OroCRM\Bundle\ContactBundle\Entity\Contact;

class DiamanteUser implements Entity
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var string
     */
    protected $activationHash;

    public function __construct($email, $username, Contact $contact = null, $firstName = null, $lastName = null)
    {
        $this->username  = $username;
        $this->email     = $email;
        $this->contact   = $contact;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->isActive    = false;
        $this->activationHash = md5($this->email . time());
    }

    /**
     * @param Contact $contact
     */
    public function assignContact(Contact $contact)
    {
        $this->contact = $contact;
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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Activate user
     * @param string $hash
     * @return void
     * @throws \RuntimeException if given hash is not equal to generated one for user
     */
    public function activate($hash)
    {
        if ($this->isActive()) {
            return;
        }
        if ($this->activationHash != $hash) {
            throw new \RuntimeException('Given hash is invalid and user can not be activated.');
        }
        $this->isActive = true;
    }
}
