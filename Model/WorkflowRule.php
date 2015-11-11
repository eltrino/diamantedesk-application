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

class WorkflowRule
{
    /**
     * @var int
     */
    protected $id;

    protected $name;

    protected $conditions;

    protected $actions;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    public function __construct($name)
    {
        $this->name = $name;
        $this->conditions = new ArrayCollection();
        $this->actions = new ArrayCollection();
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
}