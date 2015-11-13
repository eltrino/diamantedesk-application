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
namespace Diamante\AutomationBundle\Form;

use Diamante\AutomationBundle\Api\Command\RuleCommand;
use Diamante\AutomationBundle\Api\Command\ConditionCommand;
use Diamante\AutomationBundle\Api\Command\ActionCommand;
use JMS\Serializer\SerializerBuilder;
use Diamante\AutomationBundle\Model\Rule;

class CommandFactory
{
    const CONDITION_COMMAND = 'Diamante\AutomationBundle\Api\Command\ConditionCommand';
    const ACTION_COMMAND = 'array<Diamante\AutomationBundle\Api\Command\ActionCommand>';

    public function createViewRuleCommand(Rule $rule, $mode)
    {
        $command = new RuleCommand();

        $command->id = $rule->getId();
        $command->name = $rule->getName();
        $command->mode = $mode;

        $serializer = $this->getSerializer();
        $rootCondition = $rule->getRootCondition();
        if ($rootCondition) {
            $conditionCommand = ConditionCommand::createFromCondition($rootCondition);
            $command->conditions = $serializer->serialize($conditionCommand, 'json');
        }

        $actionCommand = [];
        foreach ($rule->getActions()->getValues() as $action) {
            $actionCommand[] = ActionCommand::createFromAction($action);
        }

        if (!empty($actionCommand)) {
            $command->actions = $serializer->serialize($actionCommand, 'json');
        }

        return $command;
    }

    public function createEditRuleCommand(RuleCommand $command)
    {
        $serializer = $this->getSerializer();

        if (!is_null($command->conditions)) {
            $command->conditions = $serializer->deserialize(
                $command->conditions,
                self::CONDITION_COMMAND,
                'json'
            );
        }

        if (!is_null($command->actions)) {
            $command->actions = $serializer->deserialize(
                $command->actions,
                self::ACTION_COMMAND,
                'json'
            );
        }

        return $command;
    }

    public function createLoadRuleCommand($id, $mode)
    {
        $command = new RuleCommand();
        $command->id = $id;
        $command->mode = $mode;

        return $command;
    }

    public function createCreateRuleCommand($mode)
    {
        $command = new RuleCommand();
        $command->mode = $mode;

        return $command;
    }

    private function getSerializer()
    {
        return SerializerBuilder::create()->build();
    }
}
