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


use Diamante\AutomationBundle\Entity\Group;
use Diamante\AutomationBundle\Model\Rule;
use Diamante\AutomationBundle\Rule\Condition\ConditionFactory;
use Diamante\AutomationBundle\Rule\Condition\ConditionInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

class GenericTargetEntityProvider
{
    const TARGET_ALIAS = 't';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ConditionFactory
     */
    protected $conditionFactory;

    /**
     * GenericTargetEntityProvider constructor.
     * @param Registry $registry
     * @param ConditionFactory $conditionFactory
     * @internal param EntityManager $em
     */
    public function __construct(Registry $registry, ConditionFactory $conditionFactory)
    {
        $this->em               = $registry->getManager();
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * @param Rule $rule
     * @param $targetClass
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
     * @param $targetClass
     * @return \Doctrine\ORM\Query
     */
    protected function buildQuery(Group $group, $targetClass)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select(self::TARGET_ALIAS)
            ->from($targetClass, self::TARGET_ALIAS);

        $where = $this->buildGroupCondition($qb, $group);

        $qb->where($where);

        return $qb->getQuery();
    }

    /**
     * @param QueryBuilder $qb
     * @param Group $group
     * @return Expr\Andx|Expr\Orx
     */
    protected function buildGroupCondition(QueryBuilder $qb, Group $group)
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
                $childGroupCondition = $this->buildGroupCondition($qb, $childGroup);
                $condition->add($childGroupCondition);
            }

            return $condition;
        }

        foreach ($group->getConditions() as $conditionDefinition) {
            $conditionDefinition = $this->conditionFactory->getCondition(
                $conditionDefinition->getType(),
                $conditionDefinition->getParameters()
            );
            /** @var ConditionInterface $conditionDefinition */
            list($property, $expr, $value) = $conditionDefinition->export();
            $compiledCondition = $this->buildCondition($qb, $property, $expr, $value);
            $condition->add($compiledCondition);
        }

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param $property
     * @param $expr
     * @param $value
     * @return mixed
     */
    protected function buildCondition(QueryBuilder $qb, $property, $expr, $value)
    {
        if (!method_exists($qb->expr(), $expr)) {
            throw new \RuntimeException(sprintf("Operator '%s' does not exist. Please verify export format", $expr));
        }

        if (is_array($value) && $expr !== 'in') {
            $compiledValue = [];
            foreach ($value as $subCondition) {
                list ($subProperty, $subExpr, $subValue) = $subCondition;
                $subConditionCompiled = $this->buildCondition($qb, $subProperty, $subExpr, $subValue);
                $compiledValue[] = $subConditionCompiled;
            }

            $value = $qb->expr()->andX()->addMultiple($compiledValue);
        }

        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            [sprintf("%s.%s", self::TARGET_ALIAS, $property), sprintf("'%s'", $value)]
        );

        return $condition;
    }
}