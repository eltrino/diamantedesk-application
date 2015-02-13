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

class PagingInfo
{
    /**
     * @var int
     */
    protected $totalRecords;

    /**
     * @var int
     */
    protected $nextPage;

    /**
     * @var int
     */
    protected $lastPage;

    /**
     * @var int
     */
    protected $firstPage;

    /**
     * @var int
     */
    protected $previousPage;

    /**
     * @var PagingProperties
     */
    protected $pagingConfig;

    public function __construct($totalRecords, PagingProperties $pagingConfig)
    {
        $this->totalRecords = $totalRecords;
        $this->pagingConfig = $pagingConfig;

        $this->calculatePaging();
    }

    protected function calculatePaging()
    {
        $this->lastPage = ceil($this->totalRecords/$this->pagingConfig->getLimit());
        $currentPage = $this->pagingConfig->getPage();

        if ($this->lastPage <= 1) {
            $this->nextPage     = 0;
            $this->previousPage = 0;
            $this->firstPage    = 0;
            return;
        }

        if (1 == $currentPage) {
            $this->nextPage     = $currentPage + 1;
            $this->previousPage = 0;
            $this->firstPage    = 0;
            return;
        }

        if ($currentPage == $this->lastPage) {
            $this->nextPage     = 0;
            $this->previousPage = $currentPage - 1;
            $this->firstPage    = 1;
            $this->lastPage     = 0;
            return;
        }

        $this->firstPage    = 1;
        $this->nextPage     = $currentPage + 1;
        $this->previousPage = $currentPage - 1;

        return;

    }

    /**
     * @return int
     */
    public function getTotalRecords()
    {
        return $this->totalRecords;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->nextPage;
    }

    /**
     * @return int
     */
    public function getLastPage()
    {
        return $this->lastPage;
    }

    /**
     * @return int
     */
    public function getFirstPage()
    {
        return $this->firstPage;
    }

    /**
     * @return int
     */
    public function getPreviousPage()
    {
        return $this->previousPage;
    }

    /**
     * @return PagingProperties
     */
    public function getPagingConfig()
    {
        return $this->pagingConfig;
    }
}