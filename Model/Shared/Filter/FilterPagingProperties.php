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
    const PER_PAGE_PROP_NAME = 'perPage';
    const ORDER_PROP_NAME    = 'orderByField';
    const SORT_PROP_NAME     = 'sortingOrder';

    const DEFAULT_PAGE       = 1;
    const DEFAULT_PER_PAGE   = 25;
    const DEFAULT_ORDER_BY   = 'id';
    const DEFAULT_SORT_ORDER = 'ASC';
    /**
     * @var int
     */
    protected $pageNumber;
    /**
     * @var int
     */
    protected $perPageCounter;
    /**
     * @var string
     */
    protected $orderByField;
    /**
     * @var string
     */
    protected $sortingOrder;

    public function __construct($page = null, $perPage = null, $orderByField = null, $sortingOrder = null)
    {
        $this->pageNumber       = $page ? $page : self::DEFAULT_PAGE;
        $this->perPageCounter   = $perPage ? $perPage : self::DEFAULT_PER_PAGE;
        $this->orderByField     = $orderByField ? $orderByField : self::DEFAULT_ORDER_BY;
        $this->sortingOrder     = $sortingOrder ? $sortingOrder : self::DEFAULT_SORT_ORDER;
    }

    /**
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * @return int
     */
    public function getPerPageCounter()
    {
        return $this->perPageCounter;
    }

    /**
     * @return string
     */
    public function getOrderByField()
    {
        return $this->orderByField;
    }

    /**
     * @return string
     */
    public function getSortingOrder()
    {
        return $this->sortingOrder;
    }

    /**
     * @param array $pagingConfig
     * @return FilterPagingProperties
     */
    public static function fromArray(array $pagingConfig)
    {
        return new self(
            $pagingConfig[self::PAGE_PROP_NAME],
            $pagingConfig[self::PER_PAGE_PROP_NAME],
            $pagingConfig[self::ORDER_PROP_NAME],
            $pagingConfig[self::SORT_PROP_NAME]
        );
    }
}