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

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

class SortHelper
{
    const CURRENT_CLASS_NAME = 'Diamante\DeskBundle\Datagrid\SortHelper';

    const APPLY_SORTING_FUNCTION_NAME = 'applySorting';

    const SORT_BRANCH_KEY = 'branch.key';

    const SORT_ID_KEY = 'i.sequenceNumber';

    /**
     * Retrieve callback function for sorting
     *
     * @return string[]
     */
    public static function getKeySortingFunction()
    {
        return array(self::CURRENT_CLASS_NAME, self::APPLY_SORTING_FUNCTION_NAME);
    }

    /**
     * Apply sorting for "key" grid column
     *
     * @param OrmDatasource $dataSource
     * @param $direction
     */
    public static function applySorting(OrmDatasource $dataSource, $sortingKey = null, $direction)
    {
        $qb = $dataSource->getQueryBuilder();
        $qb->addOrderBy(self::SORT_BRANCH_KEY, $direction);
        $qb->addOrderBy(self::SORT_ID_KEY, $direction);
    }
}
