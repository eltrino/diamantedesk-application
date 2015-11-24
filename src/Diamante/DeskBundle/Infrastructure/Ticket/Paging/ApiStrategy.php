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

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\TagBundle\Entity\TagManager;

class ApiStrategy extends AbstractStrategy implements Strategy
{

    /**
     * @return mixed
     */
    public function getFilterCallback()
    {
        return [$this, 'filter'];
    }

    /**
     * @return mixed
     */
    public function getCountCallback()
    {
        return [$this, 'count'];
    }

    /**
     * @param QueryBuilder $qb
     * @return mixed
     */
    public function filter(QueryBuilder $qb)
    {
        return [];
    }

    /**
     * @param QueryBuilder $qb
     * @param $entityName
     * @return mixed
     */
    public function count(QueryBuilder $qb, $entityName)
    {
        $qb->select($qb->expr()->count(DoctrineGenericRepository::SELECT_ALIAS))
            ->from($entityName, DoctrineGenericRepository::SELECT_ALIAS);
    }

    /**
     * @param $tickets
     * @param TagManager $tagManager
     * @return mixed
     */
    public function afterResult($tickets, $tagManager)
    {
        foreach ($tickets as $ticket) {
            /** @var Ticket $ticket */
            $tagManager->loadTagging($ticket);
        }

        return $tickets;
    }

}