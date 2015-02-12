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

namespace Diamante\DeskBundle\Model\Shared\Filter;

class FilterPagingProperties implements PagingProperties
{
    const PAGE_PROP_NAME     = 'page';
    const LIMIT_PROP_NAME    = 'limit';
    const SORT_PROP_NAME     = 'sort';
    const ORDER_PROP_NAME    = 'order';

    const DEFAULT_PAGE       = 1;
    const DEFAULT_LIMIT      = 25;
    const DEFAULT_SORT       = 'id';
    const DEFAULT_ORDER      = 'ASC';

    /**
     * @var array
     */
    protected $config = array();

    public function __construct($page = null, $limit = null, $sort = null, $order = null)
    {
        $this->config[self::PAGE_PROP_NAME]     = $page  ? $page  : self::DEFAULT_PAGE;
        $this->config[self::LIMIT_PROP_NAME]    = $limit ? $limit : self::DEFAULT_LIMIT;
        $this->config[self::SORT_PROP_NAME]     = $sort  ? $sort  : self::DEFAULT_SORT;
        $this->config[self::ORDER_PROP_NAME]    = $order ? $order : self::DEFAULT_ORDER;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->config[self::PAGE_PROP_NAME];
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->config[self::LIMIT_PROP_NAME];
    }

    /**
     * @return string
     */
    public function getSort()
    {
        return $this->config[self::SORT_PROP_NAME];
    }

    /**
     * @return string
     */
    public function getOrder()
    {
        return $this->config[self::ORDER_PROP_NAME];
    }

    /**
     * @param array $pagingConfig
     * @return FilterPagingProperties
     */
    public static function fromArray(array $pagingConfig)
    {
        return new self(
            $pagingConfig[self::PAGE_PROP_NAME],
            $pagingConfig[self::LIMIT_PROP_NAME],
            $pagingConfig[self::SORT_PROP_NAME],
            $pagingConfig[self::ORDER_PROP_NAME]
        );
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }
}
