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
use Diamante\AutomationBundle\Entity\WorkflowRule;
use Diamante\AutomationBundle\Entity\BusinessRule;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\BusinessAction;
use Diamante\AutomationBundle\Entity\WorkflowAction;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Oro\Bundle\CronBundle\Entity\Schedule;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RuleServiceImpl implements RuleService
{
    const MODE_BUSINESS = 'business';
    const MODE_WORKFLOW = 'workflow';
    const JOB_NAME = 'diamante:workflow:rule:run';
    const HOUR_MIN_INTERVAL = 1;
    const HOUR_MAX_INTERVAL = 24;
    const MINUTE_MIN_INTERVAL = 1;
    const MINUTE_MAX_INTERVAL = 60;
    const CRON_EXPRESSION = '*/m */h * * *';

    protected $cronExpressions
        = [
            'm' => '*/%d * * * *',
            'h' => '0 */%d * * * '
        ];

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
     * @param RegistryInterface         $registry
     * @param DoctrineGenericRepository $workflowRuleRepository
     * @param DoctrineGenericRepository $businessRuleRepository
     */
    public function __construct(
        RegistryInterface $registry,
        DoctrineGenericRepository $workflowRuleRepository,
        DoctrineGenericRepository $businessRuleRepository
    ) {
        $this->registry = $registry;
        $this->workflowRuleRepository = $workflowRuleRepository;
        $this->businessRuleRepository = $businessRuleRepository;
    }

    public function loadBusinessRule($data)
    {
        $rule = $this->businessRuleRepository->get($data['id']);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    public function loadWorkflowRule($data)
    {
        $rule = $this->workflowRuleRepository->get($data['id']);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    public function createBusinessRule($data)
    {
        $rule = new BusinessRule($data['name'], $data['target'], $data['timeInterval']);
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], $this->getBusinessActionEntity());

        $this->businessRuleRepository->store($rule);

        return $rule->getId();
    }

    public function updateBusinessRule($data)
    {
        $rule = $this->loadBusinessRule($data);
        $rule->update($data['name'], $data['frequency']);

        $rule->removeActions();
        $rule->removeRootGroup();
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], $this->getBusinessActionEntity());

        $this->businessRuleRepository->store($rule);

        return $rule->getId();
    }

    public function updateWorkflowRule($data)
    {
        $rule = $this->loadWorkflowRule($data);
        $rule->update($data['name']);

        $rule->removeActions();
        $rule->removeRootGroup();
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], $this->getWorkflowActionEntity());

        $this->workflowRuleRepository->store($rule);

        return $rule->getId();
    }

    public function createWorkflowRule($data)
    {
        $rule = new WorkflowRule($data['name'], $data['target']);
        $this->addConditions($rule, $data['conditions']);
        $this->addActions($rule, $data['actions'], $this->getWorkflowActionEntity());

        $this->workflowRuleRepository->store($rule);

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

    public function createCronJob($ruleId, $name, $timeInterval)
    {
        $command = sprintf('%s --rule-id=%d', $name, $ruleId);
        $schedule = new Schedule();
        $schedule->setCommand($command)
            ->setDefinition($this->getCronExpression($timeInterval));

        $em = $this->registry->getEntityManager();
        $em->persist($schedule);
        $em->flush();

        return $schedule;
    }

    public function actionRule($data, $action)
    {
        if ($data['mode'] !== RuleServiceImpl::MODE_BUSINESS && $data['mode'] !== RuleServiceImpl::MODE_WORKFLOW) {
            throw new \RuntimeException('Incorrect rule mode.');
        }

        $method = sprintf("%s%sRule", $action, ucfirst($data['mode']));

        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Rule action does not exists.');
        }

        $result = call_user_func([$this, $method], $data);

        return $result;
    }

    private function addConditions($rule, $data, Group $parent = null)
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

    private function addActions($rule, array $actions, $actionEntity)
    {

        foreach ($actions as $action) {
            $action = $actionEntity($action, $rule);
            $rule->addAction($action);
        }

        return $this;
    }

    private function getBusinessActionEntity()
    {
        return function ($action, $rule) {
            return new BusinessAction($action['type'], $action['parameters'], $rule);
        };
    }

    private function getWorkflowActionEntity()
    {
        return function ($action, $rule) {
            return new WorkflowAction($action['type'], $action['parameters'], $rule);
        };
    }

    private function getCronExpression($timeInterval)
    {
        preg_match('/^(\d+)(m|h)$/i', $timeInterval, $matches);
        $value = $matches[1];
        $timeUnit = $matches[2];

        switch ($timeUnit) {
            case 'm':
                $this->inRange($value, self::MINUTE_MIN_INTERVAL, self::MINUTE_MAX_INTERVAL);
                break;
            case 'h':
                $this->inRange($value, self::HOUR_MIN_INTERVAL, self::HOUR_MAX_INTERVAL);
                break;
            default:
                throw new \RuntimeException('Incorrect time interval.');
        }

        $cronExpression = sprintf($this->cronExpressions[$timeUnit], $value);

        return $cronExpression;
    }

    private function inRange($value, $min, $max)
    {
        $range = range($min, $max);
        if (!in_array($value, $range)) {
            throw new \RuntimeException('Incorrect time interval.');
        }

        return true;
    }
}
