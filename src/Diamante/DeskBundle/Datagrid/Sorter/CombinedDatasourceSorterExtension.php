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
namespace Diamante\DeskBundle\Datagrid\Sorter;

use Diamante\DeskBundle\Datagrid\CombinedUsersDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Sorter\Configuration;
use Diamante\DeskBundle\Datagrid\CombinedAuditDatasource;
use Oro\Bundle\DataGridBundle\Extension\Sorter\OrmSorterExtension;
use Oro\Bundle\DataGridBundle\Provider\State\DatagridStateProviderInterface;
use Oro\Bundle\DataGridBundle\Provider\SystemAwareResolver;

class CombinedDatasourceSorterExtension extends OrmSorterExtension
{
    public function __construct(
        DatagridStateProviderInterface $sortersStateProvider,
        SystemAwareResolver $resolver
    ){
        parent::__construct($sortersStateProvider);
        $this->resolver = $resolver;
    }

    /**
     * @param DatagridConfiguration $config
     *
     * @return bool
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $columns      = $config->offsetGetByPath(Configuration::COLUMNS_PATH);
        $type = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);
        $isApplicable = ($type == CombinedAuditDatasource::TYPE || $type == CombinedUsersDatasource::TYPE)
            && is_array($columns);

        return $isApplicable;
    }
}
