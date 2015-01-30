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

namespace Diamante\DeskBundle\Api\Internal;

use Diamante\ApiBundle\Paging\Provider\PagingContext;
use Diamante\ApiBundle\Paging\Provider\PagingContextProvider;
use Diamante\DeskBundle\Api\ApiPagingService;
use Diamante\DeskBundle\Model\Shared\Filter\PagingInfo;
use Diamante\DeskBundle\Model\Shared\Filter\PagingProperties;
use Diamante\DeskBundle\Model\Shared\FilterableRepository;

class ApiPagingServiceImpl implements ApiPagingService
{
    /**
     * @var PagingContextProvider
     */
    protected $provider;

    public function __construct(PagingContextProvider $provider)
    {
        $this->provider = $provider;
    }
    /**
     * @param FilterableRepository $repository
     * @param PagingProperties $pagingConfig
     * @param array $criteria
     * @return PagingInfo
     */
    public function getPagingInfo(FilterableRepository $repository, PagingProperties $pagingConfig, array $criteria)
    {
        $totalRecords = $repository->count($criteria);

        return new PagingInfo($totalRecords, $pagingConfig);
    }

    /**
     * @param PagingContext $context
     * @param PagingProperties $pagingConfiguration
     * @return array
     */
    private function reconstructQueryParams(PagingContext $context, PagingProperties $pagingConfiguration)
    {
        $originalQueryParams = $context->getQuery();
        $reconstructedParams = array();

        foreach ($pagingConfiguration->toArray() as $key=>$value) {
            if (array_key_exists($key, $originalQueryParams)) {
                $reconstructedParams[$key] = sprintf('%s=%s', $key, $value);
            }
        }

        if (!array_key_exists('limit', $reconstructedParams)) {
            $reconstructedParams['limit'] = sprintf('limit=%s', $pagingConfiguration->getLimit());
        }

        return $reconstructedParams;
    }

    /**
     * @param PagingInfo $pagingMetadata
     * @return string
     */
    public function createPagingLinks(PagingInfo $pagingMetadata)
    {
        $context = $this->provider->getContext();
        $params = $this->reconstructQueryParams($context, $pagingMetadata->getPagingConfig());

        $links = array();
        $pathInfo = $context->getPathInfo();
        $relations = array('first', 'last', 'next', 'previous');

        foreach ($relations as $relation) {
            $method = sprintf('get%sPage', ucfirst($relation));
            $result = call_user_func(array($pagingMetadata, $method));

            if ($result > 0) {
                $params['page'] = sprintf('page=%d', $result);
                $links[] = sprintf('<%s>; rel="%s"', $context->getUriForPath($pathInfo . '?' . join('&', array_values($params))), $relation);
            }
        }

        $linksString = join(', ', $links);

        return $linksString;
    }

    /**
     * @param PagingInfo $info
     * @param string $links
     */
    public function populatePagingHeaders(PagingInfo $info, $links)
    {
        $this->provider->getContext()->getHeaderContainer()->add(array('Link'    => $links));
        $this->provider->getContext()->getHeaderContainer()->add(array('X-Total' => $info->getTotalRecords()));
    }
}