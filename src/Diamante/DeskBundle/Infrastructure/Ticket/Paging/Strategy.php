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

namespace Diamante\DeskBundle\Infrastructure\Ticket\Paging;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\TagBundle\Entity\TagManager;

/**
 * Interface Strategy
 * @package Diamante\DeskBundle\Infrastructure\Ticket\Paging
 */
interface Strategy
{
    /**
     * @param QueryBuilder $qb
     * @return mixed
     */
    public function filter(QueryBuilder $qb);

    /**
     * @param QueryBuilder $qb
     * @param $entityName
     * @return mixed
     */
    public function count(QueryBuilder $qb, $entityName);

    /**
     * @return mixed
     */
    public function getFilterCallback();

    /**
     * @return mixed
     */
    public function getCountCallback();

    /**
     * @param $tickets
     * @param TagManager $tagManager
     * @return mixed
     */
    public function afterResult($tickets, $tagManager);
}