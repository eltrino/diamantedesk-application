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
namespace Diamante\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use \OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @ORM\Entity(repositoryClass="Diamante\ApiBundle\Infrastructure\Persistence\DoctrineApiUserRepository")
 * @ORM\Table(name="diamante_api_user")
 */
class ApiUser extends \Diamante\ApiBundle\Model\ApiUser\ApiUser
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * OroCrm Contact
     *
     * @var Contact
     *
     * @ORM\OneToOne(targetEntity="\OroCRM\Bundle\ContactBundle\Entity\Contact")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $contact;

    /**
     * Unique username for Api User
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $username;

    /**
     * Unique email for Api User
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * The salt to use for hashing
     *
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $salt;
}
