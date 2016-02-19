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

namespace Diamante\AutomationBundle\Entity;

use Diamante\AutomationBundle\Model\Target;
use Diamante\DeskBundle\Model\Shared\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Diamante\AutomationBundle\Infrastructure\Persistence\DoctrineBusinessRuleRepository")
 * @ORM\Table(name="diamante_business_rule")
 */
class BusinessRule extends \Diamante\AutomationBundle\Model\BusinessRule
{
    /**
     * @var \Rhumsaa\Uuid\Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="string", name="id")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="time_interval", type="string")
     */
    protected $timeInterval;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @ORM\OneToOne(targetEntity="Group", inversedBy="rule", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="root_group_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $grouping;

    /**
     * @ORM\OneToMany(targetEntity="BusinessAction", mappedBy="rule", orphanRemoval=true, cascade={"persist", "remove"})
     */
    protected $actions;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $target;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;
}