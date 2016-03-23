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

namespace Diamante\DeskBundle\Api;

use Diamante\DeskBundle\Model\Shared\Filter\PagingInfo;
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\DeskBundle\Model\Shared\FilterableRepository;

interface ApiPagingService
{
    /**
     * @param FilterableRepository $repository
     * @param PagingProperties $pagingConfig
     * @param array $criteria
     * @param null $searchQuery
     * @param null $countCallback
     * @return PagingInfo
     */
    public function getPagingInfo(FilterableRepository $repository, PagingProperties $pagingConfig, array $criteria, $searchQuery = null, $countCallback = null);

    /**
     * @param PagingInfo $pagingMetadata
     * @return string
     */
    public function createPagingLinks(PagingInfo $pagingMetadata);

    /**
     * @param PagingInfo $info
     * @param string $headers
     * @return void
     */
    public function populatePagingHeaders(PagingInfo $info, $headers);
}