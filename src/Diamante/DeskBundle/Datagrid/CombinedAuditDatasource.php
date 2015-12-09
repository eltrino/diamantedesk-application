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
namespace Diamante\DeskBundle\Datagrid;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\QueryConverter\YamlConverter;
use Diamante\DeskBundle\Model\Audit\AuditRepository;

/**
 * Class CombinedAuditDatasource
 *
 * @package Diamante\DeskBundle\Datagrid
 */
class CombinedAuditDatasource extends AbstractDatasource
{
    const TYPE = 'diamante_combined_audit_datasource';

    /** @var  array */
    protected $config;

    /** @var QueryBuilder */
    protected $qbOroAudit;

    /** @var QueryBuilder */
    protected $qbDiamanteAudit;

    /**
     * @var AuditRepository
     */
    protected $auditRepository;

    /**
     * @param Registry        $doctrineRegistry
     * @param AuditRepository $auditRepository
     */
    public function __construct(
        Registry $doctrineRegistry,
        AuditRepository $auditRepository
    ) {
        $this->doctrineRegistry = $doctrineRegistry;
        $this->auditRepository = $auditRepository;

        $this->qbDiamanteAudit = $auditRepository->createQueryBuilder('a');
    }

    /**
     * @param DatagridInterface $grid
     * @param array             $config
     */
    public function process(DatagridInterface $grid, array $config)
    {
        $this->config = $config;

        $queryConfig = array_intersect_key($this->config, array_flip(['query']));
        $converter = new YamlConverter();
        $this->qbOroAudit = $converter->parse($queryConfig, $this->doctrineRegistry->getManager()->createQueryBuilder('a'));

        parent::process($grid, $config);
    }

    /**
     * @return ResultRecordInterface[]
     */
    public function getResults()
    {
        $rows = [];

        $oroAudit = $this->getQbOroAudit()->getQuery()->getResult(Query::HYDRATE_ARRAY);
        $diamanteAudit = $this->getQbDiamanteAudit()->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $audit = array_merge($oroAudit, $diamanteAudit);
        $this->applySorting($audit);

        foreach ($audit as $item) {
            $rows[] = new ResultRecord($item);
        }

        return $this->applyPagination($rows);

    }

    /**
     * @return array
     */
    protected function getQbOroAudit()
    {
        return $this->qbOroAudit;
    }

    /**
     * @return array
     */
    protected function getQbDiamanteAudit()
    {
        return $this->qbDiamanteAudit;
    }

    /**
     * @return array
     */
    public function getQueryBuilders()
    {
        return [
            $this->getQbDiamanteAudit(),
            $this->getQbOroAudit()
        ];
    }
}
