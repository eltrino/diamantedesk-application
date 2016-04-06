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

namespace Diamante\DeskBundle\Datagrid\Filter;

use Diamante\DeskBundle\Datagrid\CombinedAuditDatasource;
use Diamante\DeskBundle\Datagrid\CombinedUsersDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\FilterBundle\Datasource\Orm\OrmFilterDatasourceAdapter;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;
use Oro\Bundle\FilterBundle\Grid\Extension\OrmFilterExtension;

class CombinedDatasourceFilterExtension extends OrmFilterExtension
{
    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $filters = $config->offsetGetByPath(Configuration::COLUMNS_PATH);

        if ($filters === null) {
            return false;
        }

        $type = $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH);
        return $type == CombinedUsersDatasource::TYPE
            || $type == CombinedAuditDatasource::TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $filters = $this->getFiltersToApply($config);
        $values  = $this->getValuesToApply($config);

        $datasourceAdapters = [];
        foreach ($datasource->getQueryBuilders() as $qb) {
            $datasourceAdapters[] = new OrmFilterDatasourceAdapter($qb);
        }

        foreach ($filters as $filter) {
            $value = isset($values[$filter->getName()]) ? $values[$filter->getName()] : false;

            if ($value === false) {
                continue;
            }

            $form = $filter->getForm();
            if (!$form->isSubmitted()) {
                $form->submit($value);
            }

            if (!$form->isValid()) {
                continue;
            }

            foreach ($datasourceAdapters as $datasourceAdapter) {
                $filter->apply($datasourceAdapter, $form->getData());
            }
        }
    }
}
