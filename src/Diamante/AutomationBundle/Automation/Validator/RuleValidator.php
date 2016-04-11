<?php
/*
 * Copyright (c) 2016 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Automation\Validator;


use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Infrastructure\Shared\CronExpressionMapper;
use Diamante\AutomationBundle\Model\Group;
use Diamante\AutomationBundle\Model\Rule;

class RuleValidator implements ValidatorInterface
{
    /**
     * @var AutomationConfigurationProvider
     */
    protected $configurationProvider;

    public function __construct(AutomationConfigurationProvider $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @param array $input
     * @return mixed
     */
    public function validate(array $input)
    {
        $checks = ['type', 'actions', 'grouping', 'target'];

        foreach ($checks as $check) {
            $validator = sprintf("validate%s", ucfirst($check));

            $result = call_user_func([$this, $validator], $input);

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    protected function validateType($subject)
    {
        $type = isset($subject['type']) ? $subject['type'] : null;

        if (empty($type) || !in_array($type, [Rule::TYPE_WORKFLOW, Rule::TYPE_BUSINESS])) {
            return false;
        }

        if ($type == Rule::TYPE_BUSINESS && (!array_key_exists('timeInterval', $subject) || !$this->validateTimeInterval($subject))) {
            return false;
        }

        return true;
    }

    protected function validateGrouping($subject)
    {
        $grouping = isset($subject['grouping']) ? $subject['grouping'] : null;

        if (empty($grouping) || (empty($grouping['children']) && empty($grouping['conditions']))) {
            return false;
        }

        if (!in_array($grouping['connector'], [Group::CONNECTOR_EXCLUSIVE, Group::CONNECTOR_INCLUSIVE])) {
            return false;
        }

        if (!empty($grouping['conditions'])) {
            foreach ($grouping['conditions'] as $condition) {
                if (!$this->validateCondition($condition)) {
                    return false;
                }
            }
        }

        if (!empty($grouping['children'])) {
            foreach ($grouping['children'] as $group) {
                $grouping = ['grouping' => $group];
                if (!$this->validateGrouping($grouping)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function validateCondition($condition)
    {
        $conditionType = isset($condition['type']) ? strtolower($condition['type']) : null;

        $configuredConditions = $this->configurationProvider->getConfiguredConditions();

        if (empty($conditionType) || !$configuredConditions->has($conditionType)) {
            return false;
        }

        return true;
    }

    protected function validateActions($subject)
    {
        $actions = isset($subject['actions']) ? $subject['actions'] : null;

        if (empty($actions)) {
            return false;
        }

        $configuredActions = $this->configurationProvider->getConfiguredActions();

        foreach ($actions as $action) {
            if (!isset($action['type'])) {
                return false;
            }

            if (!$configuredActions->has(strtolower($action['type']))) {
                return false;
            }
        }

        return true;
    }

    protected function validateTimeInterval($subject)
    {
        $time = isset($subject['timeInterval']) ? strtolower($subject['timeInterval']) : null;

        if (empty($time) || !in_array($time, CronExpressionMapper::getConfiguredTimeIntervals())) {
            return false;
        }

        return true;
    }

    protected function validateTarget($subject)
    {
        $target = isset($subject['target']) ? $subject['target'] : null;

        if (empty($target)) {
            return false;
        }

        try {
            $this->configurationProvider->getEntityConfiguration(strtolower($target));
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}