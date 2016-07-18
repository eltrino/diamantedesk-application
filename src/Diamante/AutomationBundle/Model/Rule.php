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
use Doctrine\ORM\Mapping as ORM;

abstract class Rule implements AutomationRule, Entity
{
    const TYPE_BUSINESS = 'business';
    const TYPE_WORKFLOW = 'workflow';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    protected $grouping;

    /**
     * @var ArrayCollection
     */
    protected $actions;

    /**
     * @var bool
     */
    protected $status;

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

    public function __construct($name, $target, $status = true)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->target = $target;
        $this->actions = new ArrayCollection();
        $this->status = $status;
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

    public function getGrouping()
    {
        return $this->grouping;
    }

    public function setGrouping($group)
    {
        $this->grouping = $group;

        return $this;
    }

    public function isActive()
    {
        return $this->status;
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

    public function removeGrouping()
    {
        $this->grouping = null;

        return $this;
    }

    /**
     * @return Entity
     */
    public function getTarget()
    {
        return $this->target;
    }

    public function activate()
    {
        $this->status = true;

        return $this;
    }

    public function deactivate()
    {
        $this->status = false;

        return $this;
    }

    /**
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}