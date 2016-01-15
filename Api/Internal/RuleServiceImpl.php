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
use Diamante\AutomationBundle\Automation\Engine;
use Diamante\AutomationBundle\Automation\Validator\RuleValidator;
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Entity\BusinessRule;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Infrastructure\Shared\CronExpressionMapper;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\DeskBundle\Model\Entity\Exception\ValidationException;
use Oro\Bundle\CronBundle\Entity\Schedule;
use Rhumsaa\Uuid\Uuid;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RuleServiceImpl implements RuleService
{
    const BUSINESSRULE_COMMAND_NAME = 'diamante:automation:business:run';

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var DoctrineGenericRepository
     */
    private $workflowRuleRepository;

    /**
     * @var DoctrineGenericRepository
     */
    private $businessRuleRepository;

    /**
     * @var RuleValidator
     */
    protected $validator;

    /**
     * @param RegistryInterface $registry
     * @param DoctrineGenericRepository $workflowRuleRepository
     * @param DoctrineGenericRepository $businessRuleRepository
     * @param RuleValidator $validator
     */
    public function __construct(
        RegistryInterface $registry,
        DoctrineGenericRepository $workflowRuleRepository,
        DoctrineGenericRepository $businessRuleRepository,
        RuleValidator $validator
    ) {
        $this->registry = $registry;
        $this->workflowRuleRepository = $workflowRuleRepository;
        $this->businessRuleRepository = $businessRuleRepository;
        $this->validator              = $validator;
    }

    /**
     * @param $data
     * @param $action
     * @return mixed
     */
    public function actionRule($data, $action)
    {
        if ($data['mode'] !== Engine::MODE_BUSINESS && $data['mode'] !== Engine::MODE_WORKFLOW) {
            throw new \RuntimeException('Incorrect rule mode.');
        }

        $method = sprintf("%s%sRule", $action, ucfirst($data['mode']));

        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Rule action does not exists.');
        }

        $result = call_user_func([$this, $method], $data);

        return $result;
    }

    /**
     * @param $id
     * @return \Diamante\AutomationBundle\Entity\BusinessRule|null
     */
    public function getBusinessRuleById($id)
    {
        $rule = $this->businessRuleRepository->get($id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param $id
     * @return \Diamante\AutomationBundle\Entity\WorkflowRule|null
     */
    public function getWorkflowRuleById($id)
    {
        $rule = $this->workflowRuleRepository->get($id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param $data
     * @return BusinessRule
     */
    protected function createBusinessRule($data)
    {
        $rule = new BusinessRule($data['name'], $data['target'], $data['timeInterval'], $data['active']);
        $this->addConditions($rule, $data['grouping']);
        $this->addActions($rule, $data['actions'], Rule::TYPE_BUSINESS);

        $this->businessRuleRepository->store($rule);

        $this->createBusinessRuleProcessingCronJob($rule->getId(), $rule->getTimeInterval());
        return $rule;
    }

    /**
     * @param $data
     * @return \Diamante\DeskBundle\Model\Shared\Entity|null
     */
    protected function updateBusinessRule($data)
    {
        if (!$this->validator->validate($data)) {
            throw new ValidationException("Given data is invalid. Can not update rule");
        }

        $rule = $this->getBusinessRuleById($data['id']);
        $rule->update($data['name'], $data['timeInterval'], $data['active']);

        $rule->removeActions();
        $rule->removeRootGroup();
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], Rule::TYPE_BUSINESS);

        $this->businessRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param $data
     * @return \Diamante\DeskBundle\Model\Shared\Entity|null
     */
    protected function updateWorkflowRule($data)
    {
        if (!$this->validator->validate($data)) {
            throw new ValidationException("Given data is invalid. Can not update rule");
        }

        $rule = $this->getWorkflowRuleById($data['id']);
        $rule->update($data['name'], $data['active']);

        $rule->removeActions();
        $rule->removeRootGroup();
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], Rule::TYPE_WORKFLOW);

        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param $data
     * @return WorkflowRule
     */
    protected function createWorkflowRule($data)
    {
        $rule = new WorkflowRule($data['name'], $data['target']);
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], Rule::TYPE_WORKFLOW);

        $this->workflowRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param $id
     */
    public function deleteBusinessRule($id)
    {
        $rule = $this->getBusinessRuleById($id);
        $this->businessRuleRepository->remove($rule);
    }

    /**
     * @param $id
     */
    public function deleteWorkflowRule($id)
    {
        $rule = $this->getWorkflowRuleById($id);
        $this->workflowRuleRepository->remove($rule);
    }

    /**
     * @param string|Uuid $ruleId
     * @param string  $timeInterval
     *
     * @return Schedule
     */
    protected function createBusinessRuleProcessingCronJob($ruleId, $timeInterval)
    {
        $command = sprintf('%s --rule-id=%s', self::BUSINESSRULE_COMMAND_NAME, $ruleId);
        $schedule = new Schedule();
        $schedule->setCommand($command)
            ->setDefinition(CronExpressionMapper::getMappedCronExpression($timeInterval));

        $em = $this->registry->getEntityManager();
        $em->persist($schedule);
        $em->flush();

        return $schedule;
    }

    /**
     * @param $rule
     * @param $data
     * @param Group|null $parent
     * @return $this
     */
    private function addConditions(Rule $rule, $data, Group $parent = null)
    {
        $group = new Group($data['connector']);
        if (is_null($parent)) {
            $rule->setRootGroup($group);
        } else {
            $parent->addChild($group);
            $group->setParent($parent);
        }

        if (!empty($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                $condition = new Condition($condition['type'], $condition['parameters'], $group);
                $group->addCondition($condition);
            }
        }

        if (!empty($data['children'])) {
            foreach ($data['children'] as $child) {
                $this->addConditions($rule, $child, $group);
            }
        }

        return $this;
    }

    /**
     * @param $rule
     * @param array $actions
     * @param $ruleType
     * @return $this
     */
    private function addActions(Rule $rule, array $actions, $ruleType)
    {

        foreach ($actions as $action) {
            $class = sprintf("Diamante\\AutomationBundle\\Entity\\%sAction", ucfirst($ruleType));
            $entity = new $class($action['name'], $action['parameters'], $rule);
            $rule->addAction($entity);
        }

        return $this;
    }

    public function viewRule($type, $id)
    {
        $rule = $type == Rule::TYPE_WORKFLOW ? $this->getWorkflowRuleById($id) : $this->getBusinessRuleById($id);

        return $rule;
    }

    /**
     * @param $input
     * @return BusinessRule|WorkflowRule|void
     */
    public function createRule($input)
    {
        $input = $this->getValidatedInput($input);

        $rule = call_user_func([$this, sprintf("create%sRule", ucfirst($input['type']))], $input);

        return $rule;
    }

    public function updateRule($input)
    {
        $input = $this->getValidatedInput($input);

        $rule = call_user_func([$this, sprintf("update%sRule", ucfirst($input['type']))], $input);

        return $rule;
    }

    public function deleteRule($type, $id)
    {
        $method = $type == Rule::TYPE_BUSINESS ? 'deleteBusinessRule' : 'deleteWorkflowRule';

        call_user_func([$this, $method], $id);
    }

    protected function getValidatedInput($input)
    {
        if (!is_array($input)) {
            $input = (array)json_decode($input, true);
        }

        if (!$this->validator->validate($input)) {
            throw new ValidationException("Given input is invalid, can not create rule.");
        }

        return $input;
    }

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
}
