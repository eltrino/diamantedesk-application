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
namespace Diamante\AutomationBundle\Infrastructure\Persistence;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Doctrine\ORM\Query;
use Diamante\AutomationBundle\Model\BusinessRuleRepository;

/**
 * Class DoctrineBusinessRulesRepository
 *
 * @package Diamante\AutomationBundle\Infrastructure\Persistence
 */
class DoctrineBusinessRulesRepository extends DoctrineGenericRepository implements BusinessRuleRepository
{
    /**
     * @return array
     */
    public function getTargetList()
    {
        $result = $this->_em
            ->createQueryBuilder()
            ->select('r.target')
            ->from('DiamanteAutomationBundle:BusinessRule', 'r')
            ->distinct('r.target')
            ->getQuery()
            ->getScalarResult();

        $targetList = [];
        foreach ($result as $item) {
            $targetList[] = $item['target'];
        }

        return $targetList;
    }
}
