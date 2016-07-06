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
use Diamante\AutomationBundle\Automation\Validator\RuleValidator;
use Diamante\AutomationBundle\Entity\BusinessRule;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Infrastructure\Shared\CronExpressionMapper;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\DeskBundle\Model\Entity\Exception\ValidationException;
use Diamante\AutomationBundle\Entity\Schedule;
use Rhumsaa\Uuid\Uuid;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RuleServiceImpl implements RuleService
{
    const BUSINESS_RULE_COMMAND_NAME = 'diamante:cron:automation:business:run';

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var DoctrineGenericRepository
     */
    private $workflowRuleRepository;

    /**
     * @var DoctrineGenericRepository
     */
    private $businessRuleRepository;

    /**
     * @var DoctrineGenericRepository
     */
    private $scheduleRepository;

    /**
     * @var RuleValidator
     */
    private $validator;

    /**
     * @param RegistryInterface         $registry
     * @param DoctrineGenericRepository $workflowRuleRepository
     * @param DoctrineGenericRepository $businessRuleRepository
     * @param DoctrineGenericRepository $scheduleRepository
     * @param RuleValidator             $validator
     */
    public function __construct(
        RegistryInterface $registry,
        DoctrineGenericRepository $workflowRuleRepository,
        DoctrineGenericRepository $businessRuleRepository,
        DoctrineGenericRepository $scheduleRepository,
        RuleValidator $validator
    ) {
        $this->registry = $registry;
        $this->workflowRuleRepository = $workflowRuleRepository;
        $this->businessRuleRepository = $businessRuleRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->validator = $validator;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return BusinessRule|WorkflowRule
     */
    public function viewRule($type, $id)
    {
        $rule = $type == Rule::TYPE_WORKFLOW ? $this->getWorkflowRuleById($id) : $this->getBusinessRuleById($id);

        return $rule;
    }

    /**
     * @param string $input
     *
     * @return BusinessRule|WorkflowRule
     */
    public function createRule($input)
    {
        $input = $this->getValidatedInput($input);

        $rule = call_user_func([$this, sprintf("create%sRule", ucfirst($input['type']))], $input);

        return $rule;
    }

    /**
     * @param string $input
     * @param string $id
     *
     * @return BusinessRule|WorkflowRule
     */
    public function updateRule($input, $id)
    {
        $input = $this->getValidatedInput($input);

        $rule = call_user_func_array([$this, sprintf("update%sRule", ucfirst($input['type']))], [$input, $id]);

        return $rule;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function deleteRule($type, $id)
    {
        $method = $type == Rule::TYPE_BUSINESS ? 'deleteBusinessRule' : 'deleteWorkflowRule';

        call_user_func([$this, $method], $id);
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return Rule
     */
    public function activateRule($type, $id)
    {
        $repo = $type == Rule::TYPE_WORKFLOW ? $this->workflowRuleRepository : $this->businessRuleRepository;

        /** @var Rule $rule */
        $rule = $repo->get($id);

        if (empty($rule)) {
            throw new EntityNotFoundException("Rule not found");
        }

        $rule->activate();
        $repo->store($rule);

        return $rule;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return Rule
     */
    public function deactivateRule($type, $id)
    {
        $repo = $type == Rule::TYPE_WORKFLOW ? $this->workflowRuleRepository : $this->businessRuleRepository;

        /** @var Rule $rule */
        $rule = $repo->get($id);

        if (empty($rule)) {
            throw new EntityNotFoundException("Rule not found");
        }

        $rule->deactivate();
        $repo->store($rule);

        return $rule;
    }

    /**
     * @param string $id
     */
    private function deleteBusinessRule($id)
    {
        $rule = $this->getBusinessRuleById($id);

        /** @var Schedule $schedule */
        foreach ($this->scheduleRepository->findByCommand(static::BUSINESS_RULE_COMMAND_NAME) as $schedule) {
            if ($rule->getId() == $schedule->getParameters()['rule-id']) {
                $this->scheduleRepository->remove($schedule);
            }
        }

        $this->businessRuleRepository->remove($rule);
    }

    /**
     * @param string $id
     */
    private function deleteWorkflowRule($id)
    {
        $rule = $this->getWorkflowRuleById($id);
        $this->workflowRuleRepository->remove($rule);
    }

    /**
     * @param string $id
     *
     * @return BusinessRule
     */
    private function getBusinessRuleById($id)
    {
        $rule = $this->businessRuleRepository->get($id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param string $id
     *
     * @return WorkflowRule
     */
    private function getWorkflowRuleById($id)
    {
        $rule = $this->workflowRuleRepository->get($id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param array $input
     *
     * @return BusinessRule
     */
    private function createBusinessRule(array $input)
    {
        $rule = new BusinessRule($input['name'], $input['target'], $input['time_interval'], $input['status']);
        $this->addGrouping($rule, $input['grouping']);
        $this->addActions($rule, $input['actions'], Rule::TYPE_BUSINESS);

        $this->businessRuleRepository->store($rule);

        $this->createBusinessRuleProcessingCronJob($rule->getId(), $rule->getTimeInterval());

        return $rule;
    }

    /**
     * @param array  $input
     * @param string $id
     *
     * @return BusinessRule
     */
    private function updateBusinessRule(array $input, $id)
    {
        $rule = $this->getBusinessRuleById($id);
        $rule->update($input['name'], $input['time_interval'], $input['status']);

        $rule->removeActions();
        $rule->removeGrouping();
        $this->addGrouping($rule, $input['grouping']);
        $this->addActions($rule, $input['actions'], Rule::TYPE_BUSINESS);

        $this->businessRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param array  $input
     * @param string $id
     *
     * @return \Diamante\DeskBundle\Model\Shared\Entity|null
     */
    private function updateWorkflowRule(array $input, $id)
    {
        $rule = $this->getWorkflowRuleById($id);
        $rule->update($input['name'], $input['status']);

        $rule->removeActions();
        $rule->removeGrouping();
        $this->addGrouping($rule, $input['grouping']);
        $this->addActions($rule, $input['actions'], Rule::TYPE_WORKFLOW);

        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param array $input
     *
     * @return WorkflowRule
     */
    private function createWorkflowRule(array $input)
    {
        $rule = new WorkflowRule($input['name'], $input['target'], $input['status']);
        $this->addGrouping($rule, $input['grouping']);
        $this->addActions($rule, $input['actions'], Rule::TYPE_WORKFLOW);

        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param Rule       $rule
     * @param array      $grouping
     * @param Group|null $parent
     *
     * @return $this
     */
    private function addGrouping(Rule $rule, array $grouping, Group $parent = null)
    {
        $group = new Group($grouping['connector']);
        if (is_null($parent)) {
            $rule->setGrouping($group);
        } else {
            $parent->addChild($group);
            $group->setParent($parent);
        }

        if (!empty($grouping['conditions'])) {
            foreach ($grouping['conditions'] as $condition) {
                $condition = new Condition($condition['type'], $condition['parameters'], $group);
                $group->addCondition($condition);
            }
        }

        if (!empty($grouping['children'])) {
            foreach ($grouping['children'] as $child) {
                $this->addGrouping($rule, $child, $group);
            }
        }

        return $this;
    }

    /**
     * @param string|Uuid $ruleId
     * @param string      $timeInterval
     *
     * @return Schedule
     */
    private function createBusinessRuleProcessingCronJob($ruleId, $timeInterval)
    {
        $schedule = new Schedule();
        $schedule->setCommand(self::BUSINESS_RULE_COMMAND_NAME)
            ->setParameters(['rule-id' => $ruleId])
            ->setDefinition(CronExpressionMapper::getMappedCronExpression($timeInterval));

        $em = $this->registry->getEntityManager();
        $em->persist($schedule);
        $em->flush();

        return $schedule;
    }

    /**
     * @param Rule   $rule
     * @param array  $actions
     * @param string $ruleType
     *
     * @return $this
     */
    private function addActions(Rule $rule, array $actions, $ruleType)
    {

        foreach ($actions as $action) {
            $class = sprintf("Diamante\\AutomationBundle\\Entity\\%sAction", ucfirst($ruleType));
            $entity = new $class($action['type'], $action['parameters'], $rule);
            $rule->addAction($entity);
        }

        return $this;
    }

    /**
     * @param string $input
     *
     * @return array
     */
    private function getValidatedInput($input)
    {
        if (!is_array($input)) {
            $input = (array)json_decode($input, true);
        }

        if (!$this->validator->validate($input)) {
            throw new ValidationException("Given input is invalid, can not create rule.");
        }

        return $input;
    }
}
