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
use Diamante\AutomationBundle\Rule\Condition\Condition;
use Diamante\DeskBundle\Model\Shared\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Diamante\AutomationBundle\Infrastructure\Persistence\DoctrineBusinessRulesRepository")
 * @ORM\Table(name="diamante_business_rule")
 */
class BusinessRule extends \Diamante\AutomationBundle\Model\BusinessRule implements Entity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var Condition
     * @ORM\Column(name="rule_condition", type="condition_type")
     */
    protected $condition;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $action;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $weight;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Diamante\AutomationBundle\Entity\BusinessRule", mappedBy="parent")
     */
    protected $children;

    /**
     * @var Target
     * @ORM\Column(type="target_type")
     */
    protected $target;

    /**
     * @var \Diamante\AutomationBundle\Model\Shared\AutomationRule
     * @ORM\ManyToOne(targetEntity="Diamante\AutomationBundle\Entity\BusinessRule", inversedBy="children")
     */
    protected $parent;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $active;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $expression;

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

    public static function getClassName()
    {
        return __CLASS__;
    }
}