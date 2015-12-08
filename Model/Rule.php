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

    protected $rootGroup;

    /**
     * @var ArrayCollection
     */
    protected $actions;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var Entity
     */
    protected $target;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct($name, $target, $active = true)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->target = $target;
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

    public function addAction($action)
    {
        $this->actions->add($action);

        return $this;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getRootGroup()
    {
        return $this->rootGroup;
    }

    public function setRootGroup($group)
    {
        $this->rootGroup = $group;

        return $this;
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

    public function removeRootGroup()
    {
        $this->rootGroup = null;

        return $this;
    }

    /**
     * @return Entity
     */
    public function getTarget()
    {
        return $this->target;
    }
}