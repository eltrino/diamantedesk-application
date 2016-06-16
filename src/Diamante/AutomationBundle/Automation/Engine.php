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
use Diamante\AutomationBundle\Infrastructure\Shared\TargetMapper;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Rule\Fact\AbstractFact;
use Diamante\AutomationBundle\Rule\Fact\BusinessFact;
use Diamante\AutomationBundle\Rule\Fact\WorkflowFact;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;

class Engine
{
    const WORKFLOW_ENTITY = 'DiamanteAutomationBundle:WorkflowRule';

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
    protected $rules;

    /**
     * @var GenericTargetEntityProvider
     */
    protected $targetProvider;

    /**
     * Engine constructor.
     *
     * @param AutomationConfigurationProvider $configurationProvider
     * @param ActionProvider                  $actionProvider
     * @param Registry                        $doctrineRegistry
     * @param ConditionFactory                $conditionFactory
     * @param Logger                          $logger
     * @param GenericTargetEntityProvider     $targetProvider
     */
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
     *
     * @return BusinessFact
     */
    public function createBusinessFact($entity)
    {
        $entityType = $this->configurationProvider->getTargetByEntity($entity);
        $entity = TargetMapper::fromEntity($entity);

        return new BusinessFact($entity, $entityType);
    }

    /**
     * @param $entityType
     * @param $action
     * @param $editor
     * @param $entityChangeset
     *
     * @return AbstractFact
     */
    public function createFact($entityType, $action, $editor, $entityChangeset)
    {
        $entity = TargetMapper::fromChangeset($entityChangeset);

        return new WorkflowFact($entity, $entityType, $action, $editor, $entityChangeset);
    }

    /**
     * @param AbstractFact $fact
     * @param Group        $group
     * @return bool
     */
    protected function doCheck(AbstractFact $fact, Group $group)
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
     * @param AbstractFact $fact
     * @param Group        $group
     *
     * @return bool
     */
    protected function checkGroup(AbstractFact $fact, Group $group)
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
     * @param AbstractFact $fact
     * @param Group        $group
     * @return bool
     */
    protected function processInclusive(AbstractFact $fact, Group $group)
    {
        foreach ($group->getConditions() as $conditionEntity) {
            $condition = $this->conditionFactory->getCondition($conditionEntity->getType(), $conditionEntity->getParameters(), $fact->getTargetType());

            if (false === $condition->isSatisfiedBy($fact)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param AbstractFact $fact
     * @param Group        $group
     * @return bool
     */
    protected function processExclusive(AbstractFact $fact, Group $group)
    {
        foreach ($group->getConditions() as $conditionEntity) {
            $condition = $this->conditionFactory->getCondition(
                $conditionEntity->getType(),
                $conditionEntity->getParameters(),
                $fact->getTargetType()
            );

            if (true === $condition->isSatisfiedBy($fact)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractFact $fact
     * @return Rule[]
     */
    protected function getRules(AbstractFact $fact)
    {
        if (empty($this->rules[$fact->getTargetType()])) {
            $repository = $this->em->getRepository(static::WORKFLOW_ENTITY);

            $this->rules[$fact->getTargetType()] = $repository->findBy(['status' => true, 'target' => $fact->getTargetType()]);
        }

        return $this->rules[$fact->getTargetType()];
    }

    /**
     * Check single entity against the set of rules
     * @param AbstractFact $fact
     * @param bool         $dryRun
     */
    public function process(AbstractFact $fact, $dryRun = false)
    {
        foreach ($this->getRules($fact) as $rule) {
            $result = $this->doCheck($fact, $rule->getGrouping());

            if (true === $result) {
                foreach ($this->actionProvider->getActions($rule) as $action) {
                    $this->scheduler->addAction($action);
                }
            }
        }

        if (!$dryRun && !$this->scheduler->isEmpty()) {
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

        if (!$dryRun && !$this->scheduler->isEmpty() && !empty($targetEntities)) {
            foreach ($targetEntities as $entity) {
                $fact = $this->createBusinessFact($entity);
                $this->scheduler->run($fact);
            }
        }

        $this->scheduler->reset();

        return count($targetEntities);
    }
}