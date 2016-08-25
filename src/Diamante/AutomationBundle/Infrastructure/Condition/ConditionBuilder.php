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

namespace Diamante\AutomationBundle\Infrastructure\Condition;

use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Infrastructure\GenericTargetEntityProvider as TargetProvider;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\UserBundle\Model\User;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConditionBuilder
{
    const UNASSIGNED = 'unassigned';

    protected $conditionTimeMap
        = [
            'gt'  => 'lt',
            'gte' => 'lte',
            'lt'  => 'gt',
            'lte' => 'gte',
        ];

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var AutomationConfigurationProvider
     */
    protected $configurationProvider;

    /** @var  ContainerInterface */
    protected $container;

    /**
     * ConditionBuilder constructor.
     *
     * @param Registry                        $registry
     * @param AutomationConfigurationProvider $configurationProvider
     * @param ContainerInterface              $container
     */
    public function __construct(
        Registry $registry,
        AutomationConfigurationProvider $configurationProvider,
        ContainerInterface $container
    ) {
        $this->em = $registry->getManager();
        $this->configurationProvider = $configurationProvider;
        $this->container = $container;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            [sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName), sprintf("?%d", $parameterNumber)]
        );
        $qb->setParameter($parameterNumber, $value);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getDatetimeCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $value = new \DateTime(sprintf("-%s hours", $value), new \DateTimeZone("UTC"));
        $expr = $this->conditionTimeMap[$expr];

        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            [sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName), sprintf("?%d", $parameterNumber)]
        );
        $qb->setParameter($parameterNumber, $value);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getEndpointCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            [sprintf("%s.%s", 'mr', $fieldName), sprintf("?%d", $parameterNumber)]
        );
        $qb->setParameter($parameterNumber, $value);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getEndpointLikeCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            [sprintf("%s.%s", 'mr', $fieldName), sprintf("?%d", $parameterNumber)]
        );
        $qb->setParameter($parameterNumber, sprintf("%%%s%%", $value));

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getBranchLikeCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            ['b.name', sprintf("?%d", $parameterNumber)]
        );
        $qb->setParameter($parameterNumber, sprintf("%%%s%%", $value));

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getLikeCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $condition = call_user_func_array(
            [$qb->expr(), $expr],
            [sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName), sprintf("?%d", $parameterNumber)]
        );
        $qb->setParameter($parameterNumber, sprintf("%%%s%%", $value));

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getAssigneeEqCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        if (self::UNASSIGNED == $value) {
            $condition = $qb->expr()->isNull(sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName));
        } else {
            $condition = call_user_func_array(
                [$qb->expr(), $expr],
                [sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName), sprintf("?%d", $parameterNumber)]
            );
            $qb->setParameter($parameterNumber, User::fromString($value)->getId());
        }

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getAssigneeNeqCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        if (self::UNASSIGNED == $value) {
            $condition = $qb->expr()->isNotNull(sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName));
        } else {
            $condition = call_user_func_array(
                [$qb->expr(), $expr],
                [sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName), sprintf("?%d", $parameterNumber)]
            );
            $qb->setParameter($parameterNumber, User::fromString($value)->getId());
        }

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getPriorityGteCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $callback = function ($weight, $rulePropertyWeight) {
            if ($weight >= $rulePropertyWeight) {
                return true;
            }

            return false;
        };

        $condition = $this->getPriorityExpr($qb, $fieldName, $value, $callback, $parameterNumber);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getPriorityGtCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $callback = function ($weight, $rulePropertyWeight) {
            if ($weight > $rulePropertyWeight) {
                return true;
            }

            return false;
        };

        $condition = $this->getPriorityExpr($qb, $fieldName, $value, $callback, $parameterNumber);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getPriorityLteCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $callback = function ($weight, $rulePropertyWeight) {
            if ($weight <= $rulePropertyWeight) {
                return true;
            }

            return false;
        };

        $condition = $this->getPriorityExpr($qb, $fieldName, $value, $callback, $parameterNumber);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $expr
     * @param string       $fieldName
     * @param string       $value
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function getPriorityLtCondition(QueryBuilder $qb, $expr, $fieldName, $value, $parameterNumber)
    {
        $callback = function ($weight, $rulePropertyWeight) {
            if ($weight < $rulePropertyWeight) {
                return true;
            }

            return false;
        };

        $condition = $this->getPriorityExpr($qb, $fieldName, $value, $callback, $parameterNumber);

        return $condition;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $fieldName
     * @param string       $value
     * @param callable     $comparisonCallback
     * @param integer      $parameterNumber
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    private function getPriorityExpr(QueryBuilder $qb, $fieldName, $value, $comparisonCallback, $parameterNumber)
    {
        $properties = [];
        $weightList = Priority::getWeightList();
        $rulePropertyWeight = Priority::getWeight($value);

        foreach ($weightList as $weight => $property) {
            if ($comparisonCallback($weight, $rulePropertyWeight)) {
                $properties[] = $property;
            }
        }

        $condition = $qb->expr()->in(
            sprintf("%s.%s", TargetProvider::TARGET_ALIAS, $fieldName),
            sprintf("?%d", $parameterNumber)
        );
        $qb->setParameter($parameterNumber, $properties);

        return $condition;
    }
}