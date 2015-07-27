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
use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Rule\Action\ActionProvider;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;
use Diamante\AutomationBundle\Rule\Provider\RuleProvider;
use Diamante\DeskBundle\Model\Shared\Entity;

class EngineImpl implements Engine
{
    const MODE_WORKFLOW = 'workflow';
    const MODE_BUSINESS = 'business';

    /**
     * @var \Diamante\AutomationBundle\Rule\Provider\RuleProvider
     */
    protected $ruleProvider;
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
    protected $rulesets;

    /**
     * @param RuleProvider   $ruleProvider
     * @param ActionProvider $actionProvider
     */
    public function __construct(
        RuleProvider $ruleProvider,
        ActionProvider $actionProvider
    )
    {
        $this->ruleProvider             = $ruleProvider;
        $this->actionProvider           = $actionProvider;
        $this->agenda                   = new Agenda();
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

        if (empty($this->rulesets[$mode][$fact->getTargetType()])) {
            $this->loadRulesets($mode, $fact);
        }

        $ruleset = $this->rulesets[$mode][$fact->getTargetType()];

        $this->prepareExecutionContext($fact);

        /** @var Rule $rule */
        foreach ($ruleset as $rule) {
            $result = $this->doCheck($fact, $rule);

            if ($result) {
                $this->executionContext->setAction($rule->getAction());
                $action = $this->actionProvider->getAction($this->executionContext);
                $this->agenda->push($action);
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
     * @param Fact $fact
     * @param Rule $rule
     *
     * @return bool
     */
    protected function doCheck(Fact $fact, Rule $rule)
    {
        if ($rule->hasChildren()) {
            switch ($rule->getExpression()) {
                case Rule::EXPRESSION_EXCLUSIVE:
                    $result = $this->processExclusive($fact, $rule->getChildren());
                    break;
                case Rule::EXPRESSION_INCLUSIVE:
                    $result = $this->processInclusive($fact, $rule->getChildren());
                    break;
                default:
                    return false;
                    break;
            }
        } else {
            $result = $rule->isSatisfiedBy($fact);
        }

        return $result;
    }

    /**
     * @param Fact $fact
     * @param      $ruleset
     *
     * @return bool
     */
    protected function processExclusive(Fact $fact, $ruleset)
    {
        /** @var Rule $rule */
        foreach ($ruleset as $rule) {
            if (!$rule->hasChildren()) {
                $result = $rule->isSatisfiedBy($fact);
            } else {
                $result = $this->doCheck($fact, $rule);
            }

            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Fact $fact
     * @param      $ruleset
     *
     * @return bool
     */
    protected function processInclusive(Fact $fact, $ruleset)
    {
        /** @var Rule $rule */
        foreach ($ruleset as $rule) {
            if (!$rule->hasChildren()) {
                $result = $rule->isSatisfiedBy($fact);
            } else {
                $result = $this->doCheck($fact, $rule);
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
     *
     * @return Fact
     */
    public function createFact(Entity $entity, $entityChangeset = [])
    {
        return new Fact($entity, $entityChangeset);
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
     * @param string $mode
     * @param Fact   $fact
     */
    protected function loadRulesets($mode, $fact)
    {
        switch ($mode) {
            case self::MODE_WORKFLOW:
                $ruleset = $this->ruleProvider->getWorkflowRules($fact);
                break;
            case self::MODE_BUSINESS:
                $ruleset = $this->ruleProvider->getBusinessRules($fact);
                break;
            default:
                throw new \RuntimeException(sprintf("RuleEngine configured to use unknown mode: %s", (string)$mode));
                break;
        }

        if (empty($ruleset)) {
            return false;
        }

        $this->rulesets[$mode][$fact->getTargetType()] = $ruleset;
    }
}