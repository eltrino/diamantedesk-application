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

namespace Diamante\ApiBundle\Paging\Provider;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PagingContext
{
    /**
     * @var HeaderBag
     */
    protected $headerContainer;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $query;

    /**
     * @var string
     */
    protected $pathInfo;

    public function __construct($hostWithSchema, $baseUrl, $pathinfo, array $queryParams)
    {
        $this->host     = $hostWithSchema;
        $this->baseUrl  = $baseUrl;
        $this->query    = $queryParams;
        $this->pathInfo = $pathinfo;
    }

    /**
     * @return HeaderBag
     */
    public function getHeaderContainer()
    {
        return $this->headerContainer;
    }

    /**
     * @param HeaderBag $headerContainer
     */
    public function setHeaderContainer(HeaderBag $headerContainer)
    {
        $this->headerContainer = $headerContainer;
    }

    /**
     * @param Request $request
     * @return PagingContext
     */
    public static function fromRequest(Request $request)
    {
        return new self(
            $request->getSchemeAndHttpHost(),
            $request->getBaseUrl(),
            $request->getPathInfo(),
            $request->query->all()
        );
    }

    /**
     * @param $path
     * @return string
     */
    public function getUriForPath($path)
    {
        return sprintf('%s%s%s', $this->host, $this->baseUrl, $path);
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }
}