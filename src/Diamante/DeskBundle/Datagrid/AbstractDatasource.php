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
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;

abstract class AbstractDatasource implements DatasourceInterface
{
    const PATH_PAGER_ORIGINAL_TOTALS = '[source][original_totals]';
    const DIRECTION_DESC = 'DESC';

    /**
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * @var array
     */
    protected $requestPagerParameters
        = [
            '_page'     => 1,
            '_per_page' => 25,
        ];

    /**
     * @var DatagridInterface
     */
    protected $grid;


    /**
     * @var array
     */
    protected $sorters = [];

    /**
     * @param DatagridInterface $grid
     * @param array $config
     */
    public function process(DatagridInterface $grid, array $config) {
        $this->grid = $grid;

        if ($pagerParameters = $grid->getParameters()->get('_pager')) {
            $this->requestPagerParameters = $pagerParameters;
        }

        $grid->setDatasource(clone $this);
    }

    /**
     * Returns data extracted via datasource
     *
     * @return array
     */
    abstract public function getResults();

    /**
     * @param array $rows
     *
     * @return array
     */
    protected function applyPagination(array $rows)
    {
        $originalTotals = count($rows);
        $this->grid->getConfig()->offsetAddToArrayByPath(static::PATH_PAGER_ORIGINAL_TOTALS, [$originalTotals]);

        if (count($rows) > $this->requestPagerParameters['_per_page']) {
            $offset = ($this->requestPagerParameters['_page'] - 1) * $this->requestPagerParameters['_per_page'];
            if ($offset < 0) {
                $offset = 0;
            }
            $rows = array_slice(
                $rows,
                $offset,
                $this->requestPagerParameters['_per_page']
            );
        }

        return $rows;
    }

    /**
     * @param array $sorters
     */
    public function setSorters($sorters)
    {
        $this->sorters = $sorters;
    }

    /**
     * @param $rows
     * @param $callback
     */
    protected function applySorting(&$rows, $callback)
    {
        if (!$rows || empty($rows)) {
            return;
        }

        foreach ($this->sorters as $definition) {
            list($direction, $sorter) = $definition;
            $sortProperty = substr($sorter['data_name'], strrpos($sorter['data_name'], '.') + 1);

            usort(
                $rows,
                function($a, $b) use ($sortProperty, $direction, $callback) {
                    $sortableArray = $callback($a, $b, $sortProperty);

                    $originalSortableArray = $sortableArray;

                    asort($sortableArray);

                    if ($direction == self::DIRECTION_DESC) {
                        return $sortableArray !== $originalSortableArray;
                    } else {
                        return $sortableArray === $originalSortableArray;
                    }
                }
            );
        }
    }
}
