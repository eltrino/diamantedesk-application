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
namespace Eltrino\DiamanteDeskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="Eltrino\DiamanteDeskBundle\Infrastructure\Persistence\DoctrineGenericRepository")
 * @ORM\Table(name="diamante_branch")
 * @Config(
 *      defaultValues={
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="DiamanteDesk"
 *          }
 *      }
 * )
 */
class Branch extends \Eltrino\DiamanteDeskBundle\Model\Branch\Branch
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
     */
    protected $name;

    /**
     * Branch description
     *
     * @var string $description
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * Branch default assignee
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="\Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="default_assignee_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $defaultAssignee;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Branch\Logo
     *
     * @ORM\Column(type="branch_logo")
     */
    protected $logo;

    /**
     * @var ArrayCollection
     */
    protected $tags;

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
