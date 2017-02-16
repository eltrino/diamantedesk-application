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
use Diamante\AutomationBundle\Entity\TimeTriggeredRule;
use Diamante\AutomationBundle\Entity\Condition;
use Diamante\AutomationBundle\Entity\EventTriggeredGroup;
use Diamante\AutomationBundle\Entity\TimeTriggeredGroup;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Entity\EventTriggeredRule;
use Diamante\AutomationBundle\Infrastructure\Shared\CronExpressionMapper;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Infrastructure\Shared\StringUtils;
use Diamante\DeskBundle\Model\Entity\Exception\EntityNotFoundException;
use Diamante\DeskBundle\Model\Entity\Exception\ValidationException;
use Diamante\AutomationBundle\Entity\Schedule;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\RegistryInterface;

class RuleServiceImpl implements RuleService
{
    use StringUtils;

    const TIME_TRIGGERED_RULE_COMMAND_NAME = 'diamante:cron:automation:time:run';

    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var DoctrineGenericRepository
     */
    private $eventTriggeredRuleRepository;

    /**
     * @var DoctrineGenericRepository
     */
    private $timeTriggeredRuleRepository;

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
     * @param DoctrineGenericRepository $eventTriggeredRuleRepository
     * @param DoctrineGenericRepository $timeTriggeredRuleRepository
     * @param DoctrineGenericRepository $scheduleRepository
     * @param RuleValidator             $validator
     */
    public function __construct(
        RegistryInterface $registry,
        DoctrineGenericRepository $eventTriggeredRuleRepository,
        DoctrineGenericRepository $timeTriggeredRuleRepository,
        DoctrineGenericRepository $scheduleRepository,
        RuleValidator $validator
    ) {
        $this->registry = $registry;
        $this->eventTriggeredRuleRepository = $eventTriggeredRuleRepository;
        $this->timeTriggeredRuleRepository = $timeTriggeredRuleRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->validator = $validator;
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return TimeTriggeredRule|EventTriggeredRule
     */
    public function viewRule($type, $id)
    {
        $rule = $type == Rule::TYPE_EVENT_TRIGGERED ? $this->getEventTriggeredRuleById($id) : $this->getTimeTriggeredRuleById($id);

        return $rule;
    }

    /**
     * @param string $input
     *
     * @return TimeTriggeredRule|EventTriggeredRule
     */
    public function createRule($input)
    {
        $input = $this->getValidatedInput($input);

        $rule = call_user_func([$this, sprintf("create%sRule", $this->camelize($input['type']))], $input);

        return $rule;
    }

    /**
     * @param string $input
     * @param string $id
     *
     * @return TimeTriggeredRule|EventTriggeredRule
     */
    public function updateRule($input, $id)
    {
        $input = $this->getValidatedInput($input);

        $rule = call_user_func_array([$this, sprintf("update%sRule", $this->camelize($input['type']))], [$input, $id]);

        return $rule;
    }

    /**
     * @param string $type
     * @param string $id
     */
    public function deleteRule($type, $id)
    {
        $method = $type == Rule::TYPE_TIME_TRIGGERED ? 'deleteTimeTriggeredRule' : 'deleteEventTriggeredRule';

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
        $repo = $type == Rule::TYPE_EVENT_TRIGGERED ? $this->eventTriggeredRuleRepository : $this->timeTriggeredRuleRepository;

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
        $repo = $type == Rule::TYPE_EVENT_TRIGGERED ? $this->eventTriggeredRuleRepository : $this->timeTriggeredRuleRepository;

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
    private function deleteTimeTriggeredRule($id)
    {
        $rule = $this->getTimeTriggeredRuleById($id);

        /** @var Schedule $schedule */
        foreach ($this->scheduleRepository->findByCommand(static::TIME_TRIGGERED_RULE_COMMAND_NAME) as $schedule) {
            if ($rule->getId() == $schedule->getParameters()['rule-id']) {
                $this->scheduleRepository->remove($schedule);
            }
        }

        $this->timeTriggeredRuleRepository->remove($rule);
    }

    /**
     * @param string $id
     */
    private function deleteEventTriggeredRule($id)
    {
        $rule = $this->getEventTriggeredRuleById($id);
        $this->eventTriggeredRuleRepository->remove($rule);
    }

    /**
     * @param string $id
     *
     * @return TimeTriggeredRule
     */
    private function getTimeTriggeredRuleById($id)
    {
        $rule = $this->timeTriggeredRuleRepository->get($id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param string $id
     *
     * @return EventTriggeredRule
     */
    private function getEventTriggeredRuleById($id)
    {
        $rule = $this->eventTriggeredRuleRepository->get($id);

        if (is_null($rule)) {
            throw new \RuntimeException('Rule loading failed. Rule not found.');
        }

        return $rule;
    }

    /**
     * @param array $input
     *
     * @return TimeTriggeredRule
     */
    private function createTimeTriggeredRule(array $input)
    {
        $rule = new TimeTriggeredRule($input['name'], $input['target'], $input['time_interval'], $input['status']);
        $group = function ($connector) {
            return new TimeTriggeredGroup($connector);
        };

        $this->addGrouping($rule, $input['grouping'], $group);
        $this->addActions($rule, $input['actions'], Rule::TYPE_TIME_TRIGGERED);

        $this->timeTriggeredRuleRepository->store($rule);

        $this->createTimeTriggeredRuleProcessingCronJob($rule->getId(), $rule->getTimeInterval());

        return $rule;
    }

    /**
     * @param array  $input
     * @param string $id
     *
     * @return TimeTriggeredRule
     */
    private function updateTimeTriggeredRule(array $input, $id)
    {
        $rule = $this->getTimeTriggeredRuleById($id);
        $group = function ($connector) {
            return new TimeTriggeredGroup($connector);
        };

        $rule->update($input['name'], $input['time_interval'], $input['status']);
        $rule->removeActions();
        $rule->removeGrouping();
        $this->addGrouping($rule, $input['grouping'], $group);
        $this->addActions($rule, $input['actions'], Rule::TYPE_TIME_TRIGGERED);

        $this->timeTriggeredRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param array  $input
     * @param string $id
     *
     * @return \Diamante\DeskBundle\Model\Shared\Entity|null
     */
    private function updateEventTriggeredRule(array $input, $id)
    {
        $rule = $this->getEventTriggeredRuleById($id);
        $group = function ($connector) {
            return new EventTriggeredGroup($connector);
        };

        $rule->update($input['name'], $input['status']);
        $rule->removeActions();
        $rule->removeGrouping();
        $this->addGrouping($rule, $input['grouping'], $group);
        $this->addActions($rule, $input['actions'], Rule::TYPE_EVENT_TRIGGERED);

        $this->eventTriggeredRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param array $input
     *
     * @return EventTriggeredRule
     */
    private function createEventTriggeredRule(array $input)
    {
        $rule = new EventTriggeredRule($input['name'], $input['target'], $input['status']);
        $group = function ($connector) {
            return new EventTriggeredGroup($connector);
        };

        $this->addGrouping($rule, $input['grouping'], $group);
        $this->addActions($rule, $input['actions'], Rule::TYPE_EVENT_TRIGGERED);

        $this->eventTriggeredRuleRepository->store($rule);

        return $rule;
    }

    /**
     * @param Rule       $rule
     * @param array      $grouping
     * @param callable   $groupInstance
     * @param Group|null $parent
     *
     * @return $this
     */
    private function addGrouping(Rule $rule, array $grouping, $groupInstance, Group $parent = null)
    {
        $group = $groupInstance($grouping['connector']);
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
                $this->addGrouping($rule, $child, $groupInstance, $group);
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
    private function createTimeTriggeredRuleProcessingCronJob($ruleId, $timeInterval)
    {
        $schedule = new Schedule();
        $schedule->setCommand(self::TIME_TRIGGERED_RULE_COMMAND_NAME)
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
            $class = sprintf("Diamante\\AutomationBundle\\Entity\\%sAction", $this->camelize($ruleType));
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
