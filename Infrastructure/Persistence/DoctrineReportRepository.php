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
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {
        $this->em = $entityManager;
        $this->container = $container;
        $this->driver = $this->em->getConnection()->getDriver()->getName();
    }

    /**
     * @return array
     */
    public function getTimeOfResponseReportData()
    {
        $route = $this->container->get('request')->get('_route');
        if ($route === 'diamante_report_widget') {
            $sql = $this->getWidgetReportSql();
        } else {
            $sql = $this->getOriginalReportSql();
        }

        return $this->execute($sql);
    }

    protected function getOriginalReportSql()
    {
        $dateDiffExpression = $this->getDateDiffExpression();
        return
            "
SELECT
  '0-1' AS data_range,
  count(t.id) as data_count,
  DATE(t.created_at) as data_date
FROM
  diamante_ticket t
  INNER JOIN (SELECT
                min(c.created_at) AS c_created_at,
                c.ticket_id       AS subquery_ticket_id
              FROM diamante_comment c
              GROUP BY c.ticket_id) s
    ON (t.id = s.subquery_ticket_id)
WHERE {$dateDiffExpression} BETWEEN 0 AND 3600
GROUP BY DATE(t.created_at)

UNION SELECT
        '1-8' AS data_range,
        count(t.id) as data_count,
        DATE(t.created_at) as data_date
      FROM
        diamante_ticket t
        INNER JOIN (SELECT
                      min(c.created_at) AS c_created_at,
                      c.ticket_id       AS subquery_ticket_id
                    FROM diamante_comment c
                    GROUP BY c.ticket_id) s
          ON (t.id = s.subquery_ticket_id)
      WHERE {$dateDiffExpression} BETWEEN 3600 AND 3600 * 8
      GROUP BY DATE(t.created_at)

UNION SELECT
        '8-24' AS data_range,
        count(t.id) as data_count,
        DATE(t.created_at) as data_date
      FROM
        diamante_ticket t
        INNER JOIN (SELECT
                      min(c.created_at) AS c_created_at,
                      c.ticket_id       AS subquery_ticket_id
                    FROM diamante_comment c
                    GROUP BY c.ticket_id) s
          ON (t.id = s.subquery_ticket_id)
      WHERE {$dateDiffExpression} BETWEEN 3600 * 8 AND 3600 * 24
      GROUP BY DATE(t.created_at)

UNION SELECT
        'more 24' AS data_range,
        count(t.id) as data_count,
        DATE(t.created_at) as data_date
      FROM
        diamante_ticket t
        INNER JOIN (SELECT
                      min(c.created_at) AS c_created_at,
                      c.ticket_id       AS subquery_ticket_id
                    FROM diamante_comment c
                    GROUP BY c.ticket_id) s
          ON (t.id = s.subquery_ticket_id)
      WHERE {$dateDiffExpression} > 3600 * 24
      GROUP BY DATE(t.created_at)
";
    }

    protected function getWidgetReportSql()
    {
        $dateDiffExpression = $this->getDateDiffExpression();
        return
            "
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
