<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Automation;


use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Infrastructure\GenericTargetEntityProvider;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Rule\Fact\Fact;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;

class Engine
{
    const MODE_WORKFLOW = 'workflow';
    const MODE_BUSINESS = 'business';

    /**
     * @var AutomationConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var ActionProvider
     */
    protected $actionProvider;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $entityMap = [
        self::MODE_WORKFLOW => 'DiamanteAutomationBundle:WorkflowRule',
        self::MODE_BUSINESS => 'DiamanteAutomationBundle:BusinessRule'
    ];

    /**
     * @var array
     */
    protected $rules;

    /**
     * @var GenericTargetEntityProvider
     */
    protected $targetProvider;

    public function __construct(
        AutomationConfigurationProvider $configurationProvider,
        ActionProvider                  $actionProvider,
        Registry                        $doctrineRegistry,
        ConditionFactory                $conditionFactory,
        Logger                          $logger,
        GenericTargetEntityProvider     $targetProvider
    )
    {
        $this->configurationProvider = $configurationProvider;
        $this->actionProvider        = $actionProvider;
        $this->em                    = $doctrineRegistry->getManager();
        $this->conditionFactory      = $conditionFactory;
        $this->logger                = $logger;
        $this->scheduler             = new Scheduler($this->logger);
        $this->targetProvider        = $targetProvider;
    }

    /**
     * @param $entity
     * @param null $entityChangeset
     * @return Fact
     */
    public function createFact($entity, $entityChangeset = null)
    {
        $entityType = $this->configurationProvider->getTargetByClass($entity);

        return new Fact($entity, $entityType, $entityChangeset);
    }

    /**
     * @param Fact $fact
     * @param Group $group
     * @return bool
     */
    protected function doCheck(Fact $fact, Group $group)
    {
        $results = [];

        if (!$group->hasChildren()) {
            $results[] = $this->checkGroup($fact, $group);
        } else {
            foreach ($group->getChildren() as $child) {
                $results[] = $child->checkGroup($fact, $group);
            }
        }

        switch ($connector = $group->getConnector()) {
            case Group::CONNECTOR_INCLUSIVE:
                $result = !in_array(false, $results);
                break;
            case Group::CONNECTOR_EXCLUSIVE:
                $result = in_array(true, $results);
                break;
            default:
                throw new \RuntimeException(sprintf("Invalid configuration detected. %s is not a valid connector", $connector));
                break;
        }

        return $result;
    }

    /**
     * @param Fact $fact
     * @param Group $group
     *
     * @return bool
     */
    protected function checkGroup(Fact $fact, Group $group)
    {
        if ($group->hasChildren()) {
            $result = $this->doCheck($fact, $group);
            return $result;
        }

        switch ($connector = $group->getConnector()) {
            case Group::CONNECTOR_INCLUSIVE:
                $result = $this->processInclusive($fact, $group);
                break;
            case Group::CONNECTOR_EXCLUSIVE:
                $result = $this->processExclusive($fact, $group);
                break;
            default:
                throw new \RuntimeException(sprintf("Invalid configuration detected. %s is not a valid connector", $connector));
                break;
        }

        return $result;
    }

    /**
     * @param Fact $fact
     * @param Group $group
     * @return bool
     */
    protected function processInclusive(Fact $fact, Group $group)
    {
        foreach ($group->getConditions() as $conditionEntity) {
            $condition = $this->conditionFactory->getCondition($conditionEntity->getType(), $conditionEntity->getParameters());

            if (false === $condition->isSatisfiedBy($fact)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Fact $fact
     * @param Group $group
     * @return bool
     */
    protected function processExclusive(Fact $fact, Group $group)
    {
        foreach ($group->getConditions() as $conditionEntity) {
            $condition = $this->conditionFactory->getCondition($conditionEntity->getType(), $conditionEntity->getParameters());

            if (true === $condition->isSatisfiedBy($fact)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Fact $fact
     * @param string $mode
     * @return Rule[]
     */
    protected function getRules(Fact $fact, $mode)
    {
        if (empty($this->rules[$fact->getTargetType()][$mode])) {
            $repository = $this->em->getRepository($this->entityMap[$mode]);

            $this->rules[$fact->getTargetType()][$mode] = $repository->findBy(['active' => true, 'target' => $fact->getTargetType()]);
        }

        return $this->rules[$fact->getTargetType()][$mode];
    }

    /**
     * Check single entity against the set of rules
     * @param Fact $fact
     * @param string $mode
     * @param bool $dryRun
     */
    public function process(Fact $fact, $mode = self::MODE_WORKFLOW, $dryRun = false)
    {
        foreach ($this->getRules($fact, $mode) as $rule) {
            $result = $this->doCheck($fact, $rule->getRootGroup());

            if (true === $result) {
                foreach ($this->actionProvider->getActions($rule) as $action) {
                    $this->scheduler->addAction($action);
                }
            }
        }

        if (!$dryRun) {
            $this->scheduler->run($fact);
            $this->scheduler->reset();
        }
    }

    /**
     * @param Rule $rule
     * @param bool|false $dryRun
     *
     * @return int
     */
    public function processRule(Rule $rule, $dryRun = false)
    {
        $target = $rule->getTarget();
        $targetClass = $this->configurationProvider->getEntityConfiguration($target)->get('class');

        $targetEntities = $this->targetProvider->getTargets($rule, $targetClass);

        foreach ($this->actionProvider->getActions($rule) as $action) {
            $this->scheduler->addAction($action);
        }

        if (!$dryRun) {
            foreach ($targetEntities as $entity) {
                $fact = $this->createFact($entity);
                $this->scheduler->run($fact);
            }
        }

        $this->scheduler->reset();

        return count($targetEntities);
    }
}