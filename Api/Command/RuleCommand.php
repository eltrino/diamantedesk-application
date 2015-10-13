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

use Diamante\AutomationBundle\Rule\Condition\Condition;
use Diamante\AutomationBundle\Model\Target;
use Diamante\AutomationBundle\Model\Shared\AutomationRule;
use Diamante\AutomationBundle\Model\Rule;
use JMS\Serializer\Annotation\Type;

/**
 * Class RuleCommand
 *
 * @package Diamante\AutomationBundle\Api\Command
 */
class RuleCommand
{
    /**
     * @var int
     *
     * @Type("integer")
     */
    public $id;

    /**
     * @var Condition
     *
     * @Type("string")
     */
    public $condition;

    /**
     * @var string
     *
     * @Type("string")
     */
    public $action;

    /**
     * @var int
     *
     * @Type("integer")
     */
    public $weight;

    /**
     * @var array
     *
     * @Type("array<Diamante\AutomationBundle\Api\Command\RuleCommand>")
     */
    public $children;

    /**
     * @var Target
     *
     * @Type("string")
     */
    public $target;

    /**
     * @var int
     *
     * @Type("integer")
     */
    public $parent;

    /**
     * @var bool
     *
     * @Type("boolean")
     */
    public $active;

    /**
     * @var string
     *
     * @Type("string")
     */
    public $expression;

    /**
     * @var string
     *
     * @Type("string")
     */
    public $mode;

    public static function createFromRule($rule)
    {
        return self::create($rule);
    }

    private static function create(Rule $rule, RuleCommand $parentCommand = null) {
        $command = new self;
        $command->id = $rule->getId();
        $command->condition = $rule->getCondition();
        $command->action = $rule->getAction();
        $command->weight = $rule->getWeight();
        $command->target = $rule->getTarget();
        $command->active = $rule->isActive();
        $command->children = [];

        if($parentCommand) {
            $command->parent = $parentCommand->id;
            $parentCommand->children[] = $command;
        }

        if ($rule->getChildren()) {
            foreach ($rule->getChildren() as $child) {
                self::create($child, $command);
            }
        }

        return $command;
    }
}
