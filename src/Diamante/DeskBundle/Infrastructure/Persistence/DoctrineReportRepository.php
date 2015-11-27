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
namespace Diamante\DeskBundle\Infrastructure\Persistence;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;

/**
 * Class DoctrineReportRepository
 * @package Diamante\DeskBundle\Infrastructure\Persistence
 */
class DoctrineReportRepository
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    private $driver;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->em = $registry->getManager();
        $this->driver = $this->em->getConnection()->getDriver()->getName();
    }

    /**
     * @return array
     */
    public function getTimeOfResponseReportData()
    {
        $dateDiffExpression = $this->getDateDiffExpression();
        return $this->execute("
        SELECT  `day`,
                coalesce(sum(`0-1`), 0)  AS '0-1',
                coalesce(sum(`1-8`), 0) as '1-8',
                coalesce(sum(`8-24`), 0) as '8-24',
                coalesce(sum(`more 24`), 0) as 'more 24' from
        (SELECT date(t.created_at) as 'day',
                count(t.id) as '0-1',
                null as '1-8',
                null as '8-24',
                null as 'more 24'
        FROM diamante_ticket t
        INNER JOIN
        (SELECT min(c.created_at) AS c_created_at,
                c.ticket_id AS subquery_ticket_id
         FROM diamante_comment c
         GROUP BY c.ticket_id) s
        ON (t.id = s.subquery_ticket_id)
        WHERE {$dateDiffExpression} BETWEEN 0 AND 3600
        UNION ALL
        SELECT date(t.created_at),
               null as '0-1',
               count(t.id) as '1-8',
               null as '8-24',
               null as 'more 24'
        FROM diamante_ticket t
        INNER JOIN
        (SELECT min(c.created_at) AS c_created_at,
                c.ticket_id AS subquery_ticket_id
         FROM diamante_comment c
         GROUP BY c.ticket_id) s
        ON (t.id = s.subquery_ticket_id)
        WHERE {$dateDiffExpression} BETWEEN 3600 AND 3600 * 8
        UNION ALL
        SELECT date(t.created_at),
               null as '0-1',
               null as '1-8',
               count(t.id) as '8-24',
               null as 'more 24'
        FROM diamante_ticket t
        INNER JOIN
        (SELECT min(c.created_at) AS c_created_at,
                c.ticket_id AS subquery_ticket_id
         FROM diamante_comment c
         GROUP BY c.ticket_id) s
         ON (t.id = s.subquery_ticket_id)
        WHERE {$dateDiffExpression} BETWEEN 3600 * 8 AND 3600 * 24
        UNION ALL
        SELECT date(t.created_at),
               null as '0-1',
               null as '1-8',
               null as '8-24',
               count(t.id) as 'more 24'
        FROM diamante_ticket t
        INNER JOIN
        (SELECT min(c.created_at) AS c_created_at,
                c.ticket_id AS subquery_ticket_id
        FROM diamante_comment c
        GROUP BY c.ticket_id) s
        ON (t.id = s.subquery_ticket_id)
        WHERE {$dateDiffExpression} > 3600 * 24) mT
        where day is NOT NULL
        GROUP BY day");
    }

    /**
     * @return array
     */
    public function getTimeOfResponseReportWidgetData()
    {
        $dateDiffExpression = $this->getDateDiffExpression();
        return $this->execute("
              SELECT '0-1'     AS data_range,
                    count(t.id) as data_count
              FROM diamante_ticket t
              INNER JOIN
              (SELECT min(c.created_at) AS c_created_at,
                      c.ticket_id AS subquery_ticket_id
              FROM diamante_comment c
              GROUP BY c.ticket_id) s
              ON (t.id = s.subquery_ticket_id)
              WHERE {$dateDiffExpression} BETWEEN 0 AND 3600
              UNION ALL
              SELECT '1-8' AS data_range,
                      count(t.id) as data_count
              FROM diamante_ticket t
              INNER JOIN
              (SELECT min(c.created_at) AS c_created_at,
                      c.ticket_id AS subquery_ticket_id
              FROM diamante_comment c
              GROUP BY c.ticket_id) s
              ON (t.id = s.subquery_ticket_id)
              WHERE {$dateDiffExpression} BETWEEN 3600 AND 3600 * 8
              UNION ALL
              SELECT '8-24' AS data_range,
                     count(t.id) as data_count
              FROM diamante_ticket t
              INNER JOIN
              (SELECT min(c.created_at) AS c_created_at,
                      c.ticket_id AS subquery_ticket_id
              FROM diamante_comment c
              GROUP BY c.ticket_id) s
              ON (t.id = s.subquery_ticket_id)
              WHERE {$dateDiffExpression} BETWEEN 3600 * 8 AND 3600 * 24
              UNION ALL
              SELECT 'more 24' AS data_range,
              count(t.id) as data_count
              FROM diamante_ticket t
              INNER JOIN
              (SELECT min(c.created_at) AS c_created_at,
                      c.ticket_id AS subquery_ticket_id
              FROM diamante_comment c
              GROUP BY c.ticket_id) s
              ON (t.id = s.subquery_ticket_id)
              WHERE {$dateDiffExpression} > 3600 * 24");
    }

    public function getTicketsByPriority()
    {
        return $this->execute("
        select day,
            sum(case when priority = 'low' then value else 0 end) as 'low',
            sum(case when priority = 'medium' then value else 0 end) as 'medium',
            sum(case when priority = 'high' then value else 0 end) as 'high'
        from (SELECT date(t.created_at) as day, t.priority, count(t.priority) as value FROM diamante_ticket t group by date(t.created_at), t.priority) mt
        group by day");
    }

    /**
     * @param $sql
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function execute($sql)
    {
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @return string
     */
    private function getDateDiffExpression()
    {
        $dateDiffExpression = '0';

        switch ($this->driver) {
            case 'pdo_mysql':
                $dateDiffExpression = 's.c_created_at - t.created_at';
                break;
            case 'pdo_pgsql':
                $dateDiffExpression = 'EXTRACT(EPOCH FROM (s.c_created_at - t.created_at))';
                break;
        }
        return $dateDiffExpression;
    }
}
