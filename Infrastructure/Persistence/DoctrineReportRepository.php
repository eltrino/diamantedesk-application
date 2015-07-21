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

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->driver = $this->em->getConnection()->getDriver()->getName();
    }

    /**
     * @return array
     */
    public function getTimeOfResponseReportData()
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

        $sql = "
SELECT
  '0-1' AS data_range,
  count(t.id) as data_count
FROM
  diamante_ticket t
  INNER JOIN (SELECT
                min(c.created_at) AS c_created_at,
                c.ticket_id       AS subquery_ticket_id
              FROM diamante_comment c
              GROUP BY c.ticket_id) s
    ON (t.id = s.subquery_ticket_id)
WHERE {$dateDiffExpression} BETWEEN 0 AND 3600

UNION SELECT
        '1-8' AS data_range,
        count(t.id) as data_count
      FROM
        diamante_ticket t
        INNER JOIN (SELECT
                      min(c.created_at) AS c_created_at,
                      c.ticket_id       AS subquery_ticket_id
                    FROM diamante_comment c
                    GROUP BY c.ticket_id) s
          ON (t.id = s.subquery_ticket_id)
      WHERE {$dateDiffExpression} BETWEEN 3600 AND 3600 * 8

UNION SELECT
        '8-24' AS data_range,
        count(t.id) as data_count
      FROM
        diamante_ticket t
        INNER JOIN (SELECT
                      min(c.created_at) AS c_created_at,
                      c.ticket_id       AS subquery_ticket_id
                    FROM diamante_comment c
                    GROUP BY c.ticket_id) s
          ON (t.id = s.subquery_ticket_id)
      WHERE {$dateDiffExpression} BETWEEN 3600 * 8 AND 3600 * 24


UNION SELECT
        'more 24' AS data_range,
        count(t.id) as data_count
      FROM
        diamante_ticket t
        INNER JOIN (SELECT
                      min(c.created_at) AS c_created_at,
                      c.ticket_id       AS subquery_ticket_id
                    FROM diamante_comment c
                    GROUP BY c.ticket_id) s
          ON (t.id = s.subquery_ticket_id)
      WHERE {$dateDiffExpression} > 3600 * 24
";

        return $this->execute($sql);
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
}
