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
namespace Diamante\DeskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
 * @ORM\Table(name="diamante_branch")
 * @ORM\EntityListeners({"Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\BranchListener"})
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="DiamanteDesk"
 *          },
 *         "dataaudit"={"auditable"=true},
 *          "tag"={
 *              "enabled"=true
 *          }
 *      }
 * )
 */
class Branch extends \Diamante\DeskBundle\Model\Branch\Branch
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Branch name
     *
     * @var string $name
     *
     * @ORM\Column(type="string", length=255)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $name;

    /**
     * Branch description
     *
     * @var string $description
     *
     * @ORM\Column(type="text", length=65535, nullable=true)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="branch_key", type="string", length=255, nullable=false, unique=true)
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $key;

    /**
     * Branch default assignee
     *
     * @var \Oro\Bundle\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="\Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="default_assignee_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={"auditable"=true}
     *      }
     * )
     */
    protected $defaultAssignee;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\Logo
     *
     * @ORM\Column(type="branch_logo")
     *
     */
    protected $logo;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    protected $sequenceNumber = 1;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    public static function getClassName()
    {
        return __CLASS__;
    }
}
