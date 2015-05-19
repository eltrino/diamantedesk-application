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
namespace Diamante\UserBundle\Model\ApiUser;

use Diamante\DeskBundle\Model\Shared\Entity;
use Symfony\Component\Security\Core\User\UserInterface;
use Diamante\DeskBundle\Model\Shared\DomainEventProvider;
use Diamante\UserBundle\Model\ApiUser\Notifications\Events\ApiUserPasswordWasChanged;

class ApiUser extends DomainEventProvider implements Entity, UserInterface
{
    const EXPIRATION_TIME = 900;//Hash expiration time in seconds (15 minutes);

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var array
     */
    protected $roles = array();

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var integer
     */
    protected $hashExpirationTime;

    public function __construct($email, $password, $salt = null)
    {
        $this->email  = $email;
        $this->password  = $password;
        $this->salt      = $salt;
        $this->isActive  = false;
        $this->hash = md5($this->email . time());
        $this->hashExpirationTime = 0;
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Method returns same value as getEmail
     * This is required bc of \Oro\Bundle\NavigationBundle\Content\TopicSender::send method
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->raise(new ApiUserPasswordWasChanged());
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {

    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Activate user
     *
     * @param string $hash
     * @return void
     * @throws \RuntimeException if given hash is not equal to generated one for user
     */
    public function activate($hash)
    {
        if ($this->isActive()) {
            return;
        }
        if ($this->hash != $hash) {
            throw new \RuntimeException('Given hash is invalid and user can not be activated.');
        }
        $this->isActive = true;
        $this->hash = '';
    }

    /**
     * Generate new activation hash for reset pass
     */
    public function generateHash()
    {
        $timestamp = time();
        $this->hash = md5($this->getEmail() . $timestamp . $this->getPassword());
        $this->hashExpirationTime = $timestamp + self::EXPIRATION_TIME;

    }

    /**
     * Set new password for user
     *
     * @param $newPassword
     * @return void
     * @throws \RuntimeException if hash is expired
     */
    public function changePassword($newPassword)
    {
        if (time() > $this->hashExpirationTime ) {
            throw new \RuntimeException('Given hash is invalid and password can not be reset.');
        }

        $this->password = $newPassword;
        $this->isActive = true;
        $this->hash = '';
        $this->hashExpirationTime = 0;
    }

}
