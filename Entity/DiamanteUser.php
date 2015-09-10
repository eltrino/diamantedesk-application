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
namespace Diamante\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository")
 * @ORM\Table(name="diamante_user")
 * @Config(
 *      defaultValues={
 *         "dataaudit"={"auditable"=true}
 *      }
 * )
 */
class DiamanteUser extends \Diamante\UserBundle\Model\DiamanteUser
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
     * Unique email for Api User
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $email;

    /**
     * First name
     *
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $firstName;

    /**
     * Last name
     *
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $lastName;

    /**
     * @var ApiUser
     * @ORM\OneToOne(targetEntity="Diamante\UserBundle\Entity\ApiUser", mappedBy="id", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="api_user", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $apiUser;

    /**
     * @var boolean
     * @ORM\Column(name="is_deleted", type="boolean", nullable=false, options={"default" = false})
     */
    protected $isDeleted = false;

    /**
     * @var
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @var
     * @ORM\Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;

    public static function getClassName()
    {
        return __CLASS__;
    }
}
