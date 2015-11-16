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

use Diamante\AutomationBundle\Model\Shared\AutomationCondition;
use Diamante\AutomationBundle\Rule\Condition\Condition as RuleCondition;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Rule\Fact\Fact;
use Doctrine\Common\Collections\ArrayCollection;
use Diamante\DeskBundle\Model\Shared\Entity;

class Condition implements AutomationCondition, Entity
{

    const EXPRESSION_INCLUSIVE = 'AND';
    const EXPRESSION_EXCLUSIVE = 'OR';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var RuleCondition
     */
    protected $condition;

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
     * @var \Diamante\AutomationBundle\Model\Shared\AutomationCondition
     */
    protected $parent;

    /**
     * @var bool
     */
    protected $active;

    protected $rule;

    /**
     * @var string
     */
    protected $expression;

    public function __construct(
        $expression = self::EXPRESSION_INCLUSIVE,
        $condition,
        $weight = 0,
        $active = true,
        $rule,
        Target $target = null,
        AutomationCondition $parent = null
    ) {
        $this->expression   = $expression;
        $this->condition    = $condition;
        $this->weight       = $weight;
        $this->target       = $target;
        $this->children     = new ArrayCollection();
        $this->parent       = $parent;
        $this->active       = $active;
        $this->rule         = $rule;
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
     * @param \Diamante\AutomationBundle\Model\Shared\AutomationCondition $condition
     * @return $this
     */
    public function addChild(AutomationCondition $condition)
    {
        $this->children->add($condition);

        return $this;
    }

    /**
     * @return AutomationCondition
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
     * @param                    $expression
     * @param null               $condition
     * @param                    $weight
     * @param                    $active
     * @param                    $rule
     * @param Target             $target
     * @param AutomationCondition|null $parent
     *
     * @throws \Exception
     */
    public function update(
        $expression,
        $condition = null,
        $weight,
        $active,
        $rule,
        Target $target,
        AutomationCondition $parent = null
    ) {
        $this->expression   = $expression;
        $this->condition    = ConditionFactory::getConditionFor($condition);
        $this->weight       = $weight;
        $this->target       = $target;
        $this->parent       = $parent;
        $this->active       = $active;
        $this->rule         = $rule;
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

    public function getRule()
    {
        return $this->rule;
    }
}