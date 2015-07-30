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
namespace Diamante\AutomationBundle\Api\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Diamante\AutomationBundle\Rule\Condition\Condition;
use Diamante\AutomationBundle\Model\Target;
use \Diamante\AutomationBundle\Model\Shared\AutomationRule;
use \Diamante\AutomationBundle\Model\Rule;

/**
 * Class RuleCommand
 *
 * @package Diamante\AutomationBundle\Api\Command
 */
class RuleCommand
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var Condition
     */
    public $condition;

    /**
     * @var string
     */
    public $action;

    /**
     * @var int
     */
    public $weight;

    /**
     * @var ArrayCollection
     */
    public $children;

    /**
     * @var Target
     */
    public $target;

    /**
     * @var AutomationRule
     */
    public $parent;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var string
     */
    public $expression;

    /**
     * @var string
     */
    public $mode;

    public static function fromRule(Rule $rule)
    {
        $command             = new self();
        $command->id         = $rule->getId();
        $command->expression = $rule->getExpression();
        $command->condition  = $rule->getCondition();
        $command->action     = $rule->getAction();
        $command->weight     = $rule->getWeight();
        $command->active     = $rule->isActive();
        $command->target     = $rule->getTarget();
        $command->parent     = $rule->getParent();
        $command->mode       = $rule->getMode();

        return $command;
    }
}
