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

namespace Diamante\AutomationBundle\Infrastructure;


use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Infrastructure\Condition\ConditionBuilder;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Rule\Condition\ConditionInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GenericTargetEntityProvider
 *
 * @package Diamante\AutomationBundle\Infrastructure
 */
class GenericTargetEntityProvider
{
    const TARGET_ALIAS = 't';
    const BUSINESS = 'business';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * @var AutomationConfigurationProvider
     */
    protected $configurationProvider;

    /** @var  ConditionBuilder */
    protected $conditionBuilder;

    /** @var  ContainerInterface */
    protected $container;

    /**
     * GenericTargetEntityProvider constructor.
     *
     * @param Registry                        $registry
     * @param ConditionFactory                $conditionFactory
     * @param AutomationConfigurationProvider $configurationProvider
     * @param ConditionBuilder                $conditionBuilder
     * @param ContainerInterface              $container
     */
    public function __construct(
        Registry $registry,
        ConditionFactory $conditionFactory,
        AutomationConfigurationProvider $configurationProvider,
        ConditionBuilder $conditionBuilder,
        ContainerInterface $container
    ) {
        $this->em = $registry->getManager();
        $this->conditionFactory = $conditionFactory;
        $this->configurationProvider = $configurationProvider;
        $this->conditionBuilder = $conditionBuilder;
        $this->container = $container;
    }

    /**
     * @param Rule $rule
     * @param      $targetClass
     *
     * @return array|null
     */
    public function getTargets(Rule $rule, $targetClass)
    {
        try {
            $query = $this->buildQuery($rule->getGrouping(), $targetClass);
            $result = $query->getResult();
        } catch (\Exception $e) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param Group $group
     * @param       $targetClass
     *
     * @return \Doctrine\ORM\Query
     */
    protected function buildQuery(Group $group, $targetClass)
    {
        $targetType = $this->configurationProvider->getTargetByClass($targetClass);
        $qb = $this->em->createQueryBuilder();

        $qb->select(self::TARGET_ALIAS, 'b')
            ->from($targetClass, self::TARGET_ALIAS)
            ->leftJoin('DiamanteDeskBundle:MessageReference', 'mr', Expr\Join::WITH, 'mr.ticket = t.id')
            ->innerJoin('t.branch', 'b');

        $where = $this->buildGroupCondition($qb, $group, $targetType);

        $qb->where($where);

        return $qb->getQuery();
    }

    /**
     * @param QueryBuilder $qb
     * @param Group        $group
     * @param string       $targetType
     *
     * @return Expr\Andx|Expr\Orx
     */
    protected function buildGroupCondition(QueryBuilder $qb, Group $group, $targetType)
    {
        $connector = $group->getConnector();

        switch ($connector) {
            case Group::CONNECTOR_INCLUSIVE:
                $condition = $qb->expr()->andX();
                break;
            case Group::CONNECTOR_EXCLUSIVE:
                $condition = $qb->expr()->orX();
                break;
            default:
                throw new \RuntimeException("Invalid configuration for rule.");
                break;
        }

        if ($group->hasChildren()) {
            foreach ($group->getChildren() as $childGroup) {
                $childGroupCondition = $this->buildGroupCondition($qb, $childGroup, $targetType);
                $condition->add($childGroupCondition);
            }

            return $condition;
        }

        foreach ($group->getConditions() as $conditionDefinition) {
            $conditionDefinition = $this->conditionFactory->getCondition(
                $conditionDefinition->getType(),
                $conditionDefinition->getParameters(),
                $targetType
            );
            /** @var ConditionInterface $conditionDefinition */
            list($property, $expr, $value) = $conditionDefinition->export();
            $compiledCondition = $this->buildCondition($qb, $property, $expr, $value, $targetType);
            $condition->add($compiledCondition);
        }

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param              $property
     * @param              $expr
     * @param              $value
     * @param string       $targetType
     *
     * @return mixed
     */
    public function buildCondition(QueryBuilder $qb, $property, $expr, $value, $targetType)
    {
        if (!method_exists($qb->expr(), $expr)) {
            throw new \RuntimeException(sprintf("Operator '%s' does not exist. Please verify export format", $expr));
        }

        $targetClass = $this->configurationProvider->getEntityConfiguration($targetType)->get('class');
        $fieldName = $this->em->getClassMetadata($targetClass)->getFieldName($property);
        $conditionsMapper = $this->container->getParameter('diamante.automation.config.conditions_mapper');

        if (isset($conditionsMapper[self::BUSINESS][$targetType][$property][$expr])) {
            $getter = $conditionsMapper[self::BUSINESS][$targetType][$property][$expr];

            if (isset($getter['service']) && isset($getter['method']) && $this->container->has($getter['service'])) {
                $conditionServiceBuilder = $this->container->get($getter['service']);
                $method = $getter['method'];

                if (method_exists($conditionServiceBuilder, $method)) {
                    return $conditionServiceBuilder->$method($qb, $expr, $fieldName, $value);
                }
            }

            throw new \RuntimeException('Invalid source data.');
        } else {
            return $this->conditionBuilder->getCondition($qb, $expr, $fieldName, $value);
        }
    }
}