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

namespace Diamante\AutomationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Diamante\AutomationBundle\Model\Shared\AutomationRule;
use Diamante\DeskBundle\Model\Shared\Entity;
use Rhumsaa\Uuid\Uuid;

abstract class Rule implements AutomationRule, Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection
     */
    protected $conditions;

    /**
     * @var ArrayCollection
     */
    protected $actions;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct($name, $active = true)
    {
        $this->id = (string)Uuid::uuid4();
        $this->name = $name;
        $this->conditions = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->active = $active;
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addCondition($condition)
    {
        $this->conditions->add($condition);

        return $this;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function addAction($action)
    {
        $this->actions->add($action);

        return $this;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function isActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function removeActions()
    {
        foreach ($this->actions as $action) {
            $this->actions->removeElement($action);
        }

        return $this;
    }

    public function removeConditions()
    {
        foreach ($this->conditions as $condition) {
            $this->conditions->removeElement($condition);
        }

        return $this;
    }
}