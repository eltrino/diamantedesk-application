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

use Diamante\DeskBundle\Model\Shared\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;

class Group implements Entity
{

    const CONNECTOR_INCLUSIVE = 'and';
    const CONNECTOR_EXCLUSIVE = 'or';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $connector;

    protected $children;

    /**
     * @var Group|null
     */
    protected $parent;

    protected $conditions;

    protected $rule;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @param string     $connector
     * @param Group|null $parent
     */
    public function __construct($connector = self::CONNECTOR_INCLUSIVE, Group $parent = null)
    {
        $this->id = Uuid::uuid4();
        $this->connector = $connector;
        $this->children = new ArrayCollection();
        $this->conditions = new ArrayCollection();
        $this->parent = $parent;
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Group $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function addChild(Group $group)
    {
        $this->children->add($group);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function getRule()
    {
        return $this->rule;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function addCondition(Condition $condition)
    {
        $this->conditions->add($condition);

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

    public function hasChildren()
    {
        return (bool)$this->children->count();
    }
}
