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

namespace Diamante\AutomationBundle\Rule\Engine;

use Diamante\AutomationBundle\Model\Agenda;
use Diamante\AutomationBundle\Model\Fact;
use Diamante\AutomationBundle\Model\Condition;
use Diamante\AutomationBundle\Model\Action;
use Diamante\AutomationBundle\Rule\Action\ActionProvider;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\AutomationBundle\Rule\Provider\ConditionProvider;
use Diamante\DeskBundle\Model\Shared\Entity;

class EngineImpl implements Engine
{
    const MODE_WORKFLOW = 'workflow';
    const MODE_BUSINESS = 'business';

    /**
     * @var \Diamante\AutomationBundle\Rule\Provider\ConditionProvider
     */
    protected $conditionProvider;
    /**
     * @var \Diamante\AutomationBundle\Rule\Action\ActionProvider
     */
    protected $actionProvider;

    /**
     * @var \Diamante\AutomationBundle\Model\Agenda
     */
    protected $agenda;

    /**
     * @var ExecutionContext
     */
    protected $executionContext;

    /**
     * @var array
     */
    protected $conditionSets;

    /**
     * @param ConditionProvider   $conditionProvider
     * @param ActionProvider      $actionProvider
     */
    public function __construct(
        ConditionProvider $conditionProvider,
        ActionProvider $actionProvider
    ) {
        $this->conditionProvider = $conditionProvider;
        $this->actionProvider = $actionProvider;
        $this->agenda = new Agenda();
    }

    /**
     * @param Fact   $fact
     * @param string $mode
     *
     * @return bool
     */
    public function check(Fact $fact, $mode = self::MODE_WORKFLOW)
    {
        $result = false;

        if (empty($this->conditionSets[$mode][$fact->getTargetType()])) {
            $this->loadConditionSets($mode, $fact);
        }

        $conditionSet = $this->conditionSets[$mode][$fact->getTargetType()];

        if (!$conditionSet) {
            return false;
        }

        $this->prepareExecutionContext($fact);

        /** @var Condition $conditionEntity */
        foreach ($conditionSet as $conditionEntity) {
            $result = $this->doCheck($fact, $conditionEntity);

            if ($result) {
                $actionsSet = $conditionEntity->getRule()->getActions();
                /** @var Action $actionEntity */
                foreach ($actionsSet as $actionEntity) {
                    $this->executionContext->setAction($actionEntity->getAction());
                    $action = $this->actionProvider->getAction($this->executionContext);
                    $this->agenda->push($action);
                }
            }
        }

        return $result;
    }

    public function runAgenda()
    {
        if (!$this->agenda->isClean()) {
            $this->agenda->run($this->executionContext);
        }

        $this->agenda->clear();
    }

    /**
     * @param Fact      $fact
     * @param Condition $conditionEntity
     *
     * @return bool
     */
    protected function doCheck(Fact $fact, Condition $conditionEntity)
    {
        if (is_null($conditionEntity->getCondition())) {
            switch ($conditionEntity->getExpression()) {
                case Condition::EXPRESSION_EXCLUSIVE:
                    $result = $this->processExclusive($fact, $conditionEntity->getChildren());
                    break;
                case Condition::EXPRESSION_INCLUSIVE:
                    $result = $this->processInclusive($fact, $conditionEntity->getChildren());
                    break;
                default:
                    return false;
                    break;
            }
        } else {
            $result = $conditionEntity->isSatisfiedBy($fact);
        }

        return $result;
    }

    /**
     * @param Fact $fact
     * @param      $conditionSet
     *
     * @return bool
     */
    protected function processExclusive(Fact $fact, $conditionSet)
    {
        /** @var Condition $conditionEntity */
        foreach ($conditionSet as $conditionEntity) {
            if ($conditionEntity->getCondition()) {
                $result = $conditionEntity->isSatisfiedBy($fact);
            } else {
                $result = $this->doCheck($fact, $conditionEntity);
            }

            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Fact $fact
     * @param      $conditionEntitySet
     *
     * @return bool
     */
    protected function processInclusive(Fact $fact, $conditionEntitySet)
    {
        /** @var Condition $conditionEntity */
        foreach ($conditionEntitySet as $conditionEntity) {
            if ($conditionEntity->getCondition()) {
                $result = $conditionEntity->isSatisfiedBy($fact);
            } else {
                $result = $this->doCheck($fact, $conditionEntity);
            }

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    public function reset()
    {
        $this->agenda = new Agenda();
        $this->executionContext = null;
    }

    /**
     * @param Entity $entity
     * @param array  $entityChangeset
     * @param string $actionType
     *
     * @return Fact
     */
    public function createFact(Entity $entity, $actionType, $entityChangeset = [])
    {
        return new Fact($entity, $entityChangeset, $actionType);
    }

    protected function getActionType($rule)
    {

    }

    /**
     * @param Fact $fact
     */
    protected function prepareExecutionContext(Fact $fact)
    {
        $this->executionContext = new ExecutionContext($fact->getTarget(), $fact->getTargetChangeset());
        $this->executionContext->addAttribute('target_type', $fact->getTargetType());
    }

    /**
     * @param $mode
     * @param $fact
     *
     * @return bool
     */
    protected function loadConditionSets($mode, $fact)
    {
        switch ($mode) {
            case self::MODE_WORKFLOW:
                $conditionSet = $this->conditionProvider->getWorkflowConditions($fact);
                break;
            case self::MODE_BUSINESS:
                $conditionSet = $this->conditionProvider->getBusinessConditions($fact);
                break;
            default:
                throw new \RuntimeException(sprintf("RuleEngine configured to use unknown mode: %s", (string)$mode));
                break;
        }

        if (empty($conditionSet)) {
            return false;
        }

        $this->conditionSets[$mode][$fact->getTargetType()] = $conditionSet;
    }
}