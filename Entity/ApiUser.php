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
use Diamante\DeskBundle\Entity\DiamanteUser;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
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
     * Diamante User
     *
     * @var DiamanteUser
     *
     * @ORM\OneToOne(targetEntity="\Diamante\DeskBundle\Entity\DiamanteUser")
     * @ORM\JoinColumn(name="diamante_user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $diamanteUser;

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
