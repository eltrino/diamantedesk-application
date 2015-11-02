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

use Diamante\AutomationBundle\Model\Shared\AutomationRule;
use Diamante\AutomationBundle\Rule\Condition\Condition;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Rule\Fact\Fact;
use Doctrine\Common\Collections\ArrayCollection;
use Diamante\DeskBundle\Model\Shared\Entity;

class Rule implements AutomationRule, Entity
{

    const EXPRESSION_INCLUSIVE = 'AND';
    const EXPRESSION_EXCLUSIVE = 'OR';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Condition
     */
    protected $condition;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $weight;

    /**
     * @var ArrayCollection
     */
    protected $children;

    /**
     * @var Target
     */
    protected $target;

    /**
     * @var \Diamante\AutomationBundle\Model\Shared\AutomationRule
     */
    protected $parent;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @param                                                        $expression
     * @param                                                        $condition
     * @param                                                        $action
     * @param int                                                    $weight
     * @param bool                                                   $active
     * @param \Diamante\AutomationBundle\Model\Target                $target
     * @param \Diamante\AutomationBundle\Model\Shared\AutomationRule $parent
     * @throws \Exception
     */
    public function __construct(
        $expression = self::EXPRESSION_INCLUSIVE,
        $condition,
        $action,
        $weight = 0,
        $active = true,
        Target $target = null,
        AutomationRule $parent = null
    ) {
        $this->expression   = $expression;
        $this->condition    = $condition;
        $this->action       = $action;
        $this->weight       = $weight;
        $this->target       = $target;
        $this->children     = new ArrayCollection();
        $this->parent       = $parent;
        $this->active       = $active;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param \Diamante\AutomationBundle\Model\Shared\AutomationRule $rule
     * @return $this
     */
    public function addChild(AutomationRule $rule)
    {
        $this->children->add($rule);

        return $this;
    }

    /**
     * @return AutomationRule
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param                                                        $expression
     * @param                                                        $condition
     * @param                                                        $action
     * @param                                                        $weight
     * @param                                                        $active
     * @param \Diamante\AutomationBundle\Model\Target                $target
     * @param \Diamante\AutomationBundle\Model\Shared\AutomationRule $parent
     */
    public function update(
        $expression,
        $condition,
        $action,
        $weight,
        $active,
        Target $target,
        AutomationRule $parent = null
    ) {
        $this->expression   = $expression;
        $this->condition    = ConditionFactory::getConditionFor($condition);
        $this->action       = $action;
        $this->weight       = $weight;
        $this->target       = $target;
        $this->parent       = $parent;
        $this->active       = $active;
    }

    public function isSatisfiedBy(Fact $fact)
    {
        return $this->condition->isSatisfiedBy($fact);
    }

    public function activate()
    {
        $this->active = true;
    }

    public function deactivate()
    {
        $this->active = false;
    }

    public function hasChildren()
    {
        return count($this->getChildren());
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

    public function getName()
    {
        return 'rule name';
    }
}