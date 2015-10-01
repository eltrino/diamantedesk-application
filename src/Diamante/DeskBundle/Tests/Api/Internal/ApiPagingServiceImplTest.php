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

namespace Api\Internal;

use Diamante\DeskBundle\Api\Internal\ApiPagingServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\HttpFoundation\HeaderBag;

class ApiPagingServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_PATHINFO = '/api/latest/resource.json';
    const DUMMY_SERVER   = 'http://localhost';

    /**
     * @var \Diamante\ApiBundle\Paging\Provider\PagingContextProvider
     * @Mock Diamante\ApiBundle\Paging\Provider\PagingContextProvider
     */
    private $pagingProvider;

    /**
     * @var \Diamante\ApiBundle\Paging\Provider\PagingContext
     * @Mock Diamante\ApiBundle\Paging\Provider\PagingContext
     */
    private $pagingContext;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Filter\PagingProperties
     * @Mock Diamante\DeskBundle\Model\Shared\Filter\PagingProperties
     */
    private $pagingProperties;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Filter\PagingInfo
     * @Mock Diamante\DeskBundle\Model\Shared\Filter\PagingInfo
     */
    private $pagingInfo;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\FilterableRepository
     * @Mock Diamante\DeskBundle\Model\Shared\FilterableRepository
     */
    private $entityRepository;

    /**
     * @var \Diamante\DeskBundle\Api\ApiPagingService
     */
    private $apiPagingServiceImpl;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->apiPagingServiceImpl = new ApiPagingServiceImpl($this->pagingProvider);
    }

    /**
     * @test
     */
    public function testGetPagingInfo()
    {
        $this->entityRepository
            ->expects($this->once())
            ->method('count')
            ->with($this->equalTo(array(array('status', 'eq', 'new'))))
            ->will($this->returnValue(150));

        $this->pagingProperties
            ->expects($this->once())
            ->method('getLimit')
            ->will($this->returnValue(25));

        $this->pagingProperties
            ->expects($this->once())
            ->method('getPage')
            ->will($this->returnValue(3));

        $pagingInfo = $this->apiPagingServiceImpl->getPagingInfo($this->entityRepository, $this->pagingProperties, array(array('status', 'eq', 'new')));

        $this->assertInstanceOf('Diamante\DeskBundle\Model\Shared\Filter\PagingInfo', $pagingInfo);
        $this->assertEquals(150, $pagingInfo->getTotalRecords());
        $this->assertEquals(6, $pagingInfo->getLastPage());
        $this->assertEquals(1, $pagingInfo->getFirstPage());
        $this->assertEquals(4, $pagingInfo->getNextPage());
        $this->assertEquals(2, $pagingInfo->getPreviousPage());
        $this->assertInstanceOf('Diamante\DeskBundle\Model\Shared\Filter\PagingProperties', $pagingInfo->getPagingConfig());
    }

    /**
     * @test
     */
    public function testCreatePagingLinks()
    {
        $pathToLinkMapping = [
            [$this->createPageLink(1), self::DUMMY_SERVER . $this->createPageLink(1)],
            [$this->createPageLink(6), self::DUMMY_SERVER . $this->createPageLink(6)],
            [$this->createPageLink(4), self::DUMMY_SERVER . $this->createPageLink(4)],
            [$this->createPageLink(2), self::DUMMY_SERVER . $this->createPageLink(2)],
        ];

        $this->pagingInfo
            ->expects($this->once())
            ->method('getPagingConfig')
            ->will($this->returnValue($this->pagingProperties));

        $this->pagingProvider
            ->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($this->pagingContext));

        $this->pagingContext
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($this->getStubQueryParams()));

        $this->pagingProperties
            ->expects($this->atLeastOnce())
            ->method('toArray')
            ->will($this->returnValue($this->getStubPagingConfig()));

        $this->pagingProperties
            ->expects($this->once())
            ->method('getLimit')
            ->will($this->returnValue(25));

        $this->pagingContext
            ->expects($this->once())
            ->method('getPathInfo')
            ->will($this->returnValue(self::DUMMY_PATHINFO));

        $this->pagingContext
            ->expects($this->atLeastOnce())
            ->method('getUriForPath')
            ->will($this->returnValueMap($pathToLinkMapping));

        $this->pagingInfo
            ->expects($this->once())
            ->method('getFirstPage')
            ->will($this->returnValue(1));

        $this->pagingInfo
            ->expects($this->once())
            ->method('getLastPage')
            ->will($this->returnValue(6));

        $this->pagingInfo
            ->expects($this->once())
            ->method('getNextPage')
            ->will($this->returnValue(4));

        $this->pagingInfo
            ->expects($this->once())
            ->method('getPreviousPage')
            ->will($this->returnValue(2));

        $actualLinks = $this->apiPagingServiceImpl->createPagingLinks($this->pagingInfo);

        $expectedLinks = $this->getLinksString($pathToLinkMapping);

        $this->assertEquals($expectedLinks, $actualLinks);
    }

    /**
     * @test
     */
    public function testPopulatePagingHeaders()
    {
        $pathToLinkMapping = [
            [$this->createPageLink(1), self::DUMMY_SERVER . $this->createPageLink(1)],
            [$this->createPageLink(6), self::DUMMY_SERVER . $this->createPageLink(6)],
            [$this->createPageLink(4), self::DUMMY_SERVER . $this->createPageLink(4)],
            [$this->createPageLink(2), self::DUMMY_SERVER . $this->createPageLink(2)],
        ];

        $linksString = $this->getLinksString($pathToLinkMapping);

        $expectedHeaders = array(
            'link'    => array($linksString),
            'x-total' => array(150),
        );

        $headers = new HeaderBag();


        $this->pagingProvider
            ->expects($this->exactly(2))
            ->method('getContext')
            ->will($this->returnValue($this->pagingContext));

        $this->pagingContext
            ->expects($this->exactly(2))
            ->method('getHeaderContainer')
            ->will($this->returnValue($headers));

        $this->pagingInfo
            ->expects($this->once())
            ->method('getTotalRecords')
            ->will($this->returnValue(150));

        $this->apiPagingServiceImpl->populatePagingHeaders($this->pagingInfo, $linksString);

        $this->assertEquals($expectedHeaders, $headers->all());
    }

    /**
     * @return array
     */
    protected function getStubQueryParams()
    {
        return [
            'page'  => 1,
            'sort'  => 'id',
            'order' => 'ASC'
        ];
    }

    /**
     * @return array
     */
    protected function getConstructedQueryParams()
    {
        return [
            'sort'  => 'sort=id',
            'order' => 'order=ASC',
            'limit' => 'limit=25'
        ];
    }

    /**
     * @return array
     */
    protected function getStubPagingConfig()
    {
        return [
            'page'  => 1,
            'sort'  => 'id',
            'order' => 'ASC',
            'limit' => 25,
        ];
    }

    /**
     * @param $page
     * @return string
     */
    protected function createPageLink($page)
    {
        return sprintf('%s?page=%d&%s', self::DUMMY_PATHINFO, $page, join('&', $this->getConstructedQueryParams()));
    }

    /**
     * @param array $paths
     * @return string
     */
    protected function getLinksString(array $paths)
    {
        $links = $generatedLinks = [];
        for ($i = 0; $i < count($paths); $i++) {
            $generatedLinks[] = $paths[$i][1];
        }

        $relations = array('first', 'last', 'next', 'previous');
        $paths = array_combine($relations, $generatedLinks);

        foreach ($relations as $rel) {
            $links[] = sprintf('<%s>; rel="%s"', $paths[$rel], $rel);
        }

        return join(', ', $links);
    }
}