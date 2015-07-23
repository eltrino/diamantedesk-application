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
namespace Diamante\DeskBundle\Datagrid\Pager;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\PagerInterface;
use Oro\Bundle\DataGridBundle\Extension\Pager\OrmPagerExtension;
use Oro\Bundle\DataGridBundle\Extension\Toolbar\ToolbarExtension;
use Diamante\DeskBundle\Datagrid\CombinedUsersDatasource;


class CombinedUsersPagerExtension extends OrmPagerExtension
{

    /**
     * @param DatagridConfiguration $config
     * @return bool
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        return $config->offsetGetByPath(Builder::DATASOURCE_TYPE_PATH) === CombinedUsersDatasource::TYPE;
    }

    /**
     * @param DatagridConfiguration $config
     * @param DatasourceInterface $datasource
     * @return mixed|void
     */
    public function visitDatasource(DatagridConfiguration $config, DatasourceInterface $datasource)
    {
        $defaultPerPage = $config->offsetGetByPath(ToolbarExtension::PAGER_DEFAULT_PER_PAGE_OPTION_PATH, 10);
        $this->pager->setPage($this->getOr(PagerInterface::PAGE_PARAM, 1));
        $this->pager->setMaxPerPage($this->getOr(PagerInterface::PER_PAGE_PARAM, $defaultPerPage));
    }

    /**
     * @param DatagridConfiguration $config
     * @param ResultsObject $result
     * @return mixed|void
     */
    public function visitResult(DatagridConfiguration $config, ResultsObject $result)
    {
        $result->offsetSetByPath(
            PagerInterface::TOTAL_PATH_PARAM,
            $config->offsetGetByPath(CombinedUsersDatasource::PATH_PAGER_ORIGINAL_TOTALS . '[0]')
        );
    }
}
