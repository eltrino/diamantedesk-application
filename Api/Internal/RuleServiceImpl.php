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
    )
    {
        $this->workflowRuleRepository = $workflowRuleRepository;
        $this->businessRuleRepository = $businessRuleRepository;
    }

    /**
     * @param RuleCommand $command
     *
     * @return \Diamante\AutomationBundle\Model\Rule
     */
    public function loadBusinessRule(RuleCommand $command)
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
    public function loadWorkflowRule(RuleCommand $command)
    {
        $rule = $this->workflowRuleRepository->get($command->id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    public function createBusinessRule(RuleCommand $command)
    {
        $rule = new BusinessRule(
            $command->expression,
            $command->condition,
            $command->action,
            $command->weight,
            $command->active,
            $command->target,
            $command->parent
        );

        $this->businessRuleRepository->store($rule);

        return $rule;
    }

    public function createWorkflowRule(RuleCommand $command)
    {
        $rule = new WorkflowRule(
            $command->expression,
            $command->condition,
            $command->action,
            $command->weight,
            $command->active,
            $command->target,
            $command->parent
        );

        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    public function updateWorkflowRule(RuleCommand $command)
    {
        $rule = $this->loadWorkflowRule($command);
        $rule->update(
            $command->expression,
            $command->condition,
            $command->action,
            $command->weight,
            $command->active,
            $command->target,
            $command->parent
        );

        $this->workflowRuleRepository->store($rule);
    }

    public function updateBusinessRule(RuleCommand $command)
    {
        $rule = $this->loadBusinessRule($command);
        $rule->update(
            $command->expression,
            $command->condition,
            $command->action,
            $command->weight,
            $command->active,
            $command->target,
            $command->parent
        );

        $this->businessRuleRepository->store($rule);
    }

    public function deleteBusinessRule(RuleCommand $command) {
        $rule = $this->loadBusinessRule($command);
        $this->businessRuleRepository->remove($rule);
    }

    public function deleteWorkflowRule(RuleCommand $command) {
        $rule = $this->loadWorkflowRule($command);
        $this->workflowRuleRepository->remove($rule);
    }

    public function activateWorkflowRule(RuleCommand $command) {
        $rule = $this->loadWorkflowRule($command);
        $rule->activate();
        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    public function activateBusinessRule(RuleCommand $command) {
        $rule = $this->loadBusinessRule($command);
        $rule->activate();
        $this->businessRuleRepository->store($rule);

        return $rule;
    }

    public function deactivateWorkflowRule(RuleCommand $command) {
        $rule = $this->loadWorkflowRule($command);
        $rule->deactivate();
        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    public function deactivateBusinessRule(RuleCommand $command) {
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
    public function actionRule(RuleCommand $command, $action) {
        if ($command->mode !== EngineImpl::MODE_BUSINESS && $command->mode !== EngineImpl::MODE_WORKFLOW) {
            throw new \RuntimeException('Incorrect rule mode.');
        }

        $method = sprintf("%s%sRule",$action, ucfirst($command->mode));

        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Rule action does not exists.');
        }

        $result = call_user_func([$this, $method], $command);

        return $result;
    }

}
