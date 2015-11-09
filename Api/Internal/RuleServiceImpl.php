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
namespace Diamante\AutomationBundle\Api\Internal;

use Diamante\AutomationBundle\Api\RuleService;
use Diamante\AutomationBundle\Api\Command\RuleCommand;
use Diamante\AutomationBundle\Rule\Engine\EngineImpl;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Entity\BusinessRule;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\AutomationBundle\Model\Target;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;

class RuleServiceImpl implements RuleService
{
    /**
     * @var string
     */
    protected $mode;
    /**
     * @var DoctrineGenericRepository
     */
    private $workflowRuleRepository;
    /**
     * @var DoctrineGenericRepository
     */
    private $businessRuleRepository;


    public function __construct(
        DoctrineGenericRepository $workflowRuleRepository,
        DoctrineGenericRepository $businessRuleRepository
    ) {
        $this->workflowRuleRepository = $workflowRuleRepository;
        $this->businessRuleRepository = $businessRuleRepository;
    }

    /**
     * @param RuleCommand $command
     *
     * @return \Diamante\AutomationBundle\Model\Rule
     */
    public function loadBusinessRule($command)
    {
        $rule = $this->businessRuleRepository->get($command->id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param RuleCommand $command
     *
     * @return \Diamante\AutomationBundle\Model\Rule
     */
    public function loadWorkflowRule($command)
    {
        $rule = $this->workflowRuleRepository->get($command->id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    public function createBusinessRule(RuleCommand $command)
    {
        $rule = function ($command, $condition) {
            return new BusinessRule(
                $command->expression,
                $condition,
                null,
                $command->weight,
                $command->active,
                new Target($command->target),
                $command->parent
            );
        };

        return $this->updateConditions($command->conditions, $rule);
    }

    public function createWorkflowRule(RuleCommand $command)
    {
        $rule = function ($command, $condition) {
            return new WorkflowRule(
                $command->expression,
                $condition,
                null,
                $command->weight,
                $command->active,
                new Target($command->target),
                $command->parent
            );
        };

        return $this->updateConditions($command->conditions, $rule);
    }

    public function updateWorkflowRule(RuleCommand $command)
    {
        $rule = function ($command, $condition) {
            return new WorkflowRule(
                $command->expression,
                $condition,
                null,
                $command->weight,
                $command->active,
                new Target($command->target),
                $command->parent
            );
        };

        $onUpdate = function ($command) {
            if (is_null($command->parent)) {
                $this->deleteWorkflowRule($command);
            }
        };

        return $this->updateConditions($command->conditions, $rule, $onUpdate);
    }

    public function updateBusinessRule(RuleCommand $command)
    {
        $rule = function ($command, $condition) {
            return new BusinessRule(
                $command->expression,
                $condition,
                null,
                $command->weight,
                $command->active,
                new Target($command->target),
                $command->parent
            );
        };

        $onUpdate = function ($command) {
            if (is_null($command->parent)) {
                $this->deleteBusinessRule($command);
            }
        };

        return $this->updateConditions($command->conditions, $rule, $onUpdate);
    }

    private function updateConditions($command, $newRule, $onUpdate = null)
    {
        if (is_callable($onUpdate)) {
            $onUpdate($command);
        }

        $condition = ConditionFactory::create($command->condition, $command->property, $command->value);

        $rule = $newRule($command, $condition);

        $this->workflowRuleRepository->store($rule);

        if ($command->children) {
            foreach ($command->children as $child) {
                $child->parent = $rule;
                $this->updateConditions($child, $newRule);
            }
        }

        return $rule->getId();
    }

    public function deleteBusinessRule($command)
    {
        $rule = $this->loadBusinessRule($command);
        $this->businessRuleRepository->remove($rule);
    }

    public function deleteWorkflowRule($command)
    {
        $rule = $this->loadWorkflowRule($command);
        $this->workflowRuleRepository->remove($rule);
    }

    public function activateWorkflowRule(RuleCommand $command)
    {
        $rule = $this->loadWorkflowRule($command);
        $rule->activate();
        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    public function activateBusinessRule(RuleCommand $command)
    {
        $rule = $this->loadBusinessRule($command);
        $rule->activate();
        $this->businessRuleRepository->store($rule);

        return $rule;
    }

    public function deactivateWorkflowRule(RuleCommand $command)
    {
        $rule = $this->loadWorkflowRule($command);
        $rule->deactivate();
        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    public function deactivateBusinessRule(RuleCommand $command)
    {
        $rule = $this->loadBusinessRule($command);
        $rule->deactivate();
        $this->businessRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param RuleCommand $command
     * @param             $action
     *
     * @return \Diamante\AutomationBundle\Model\Rule|void
     * @throws \Exception
     */
    public function actionRule(RuleCommand $command, $action)
    {
        if ($command->mode !== EngineImpl::MODE_BUSINESS && $command->mode !== EngineImpl::MODE_WORKFLOW) {
            throw new \RuntimeException('Incorrect rule mode.');
        }

        $method = sprintf("%s%sRule", $action, ucfirst($command->mode));

        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Rule action does not exists.');
        }

        $result = call_user_func([$this, $method], $command);

        return $result;
    }
}
