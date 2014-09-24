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
namespace Eltrino\DiamanteDeskBundle\Tests\Ticket\Api\Internal;

use Doctrine\Common\Collections\ArrayCollection;
use Eltrino\DiamanteDeskBundle\Entity\Filter;
use Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\TicketGridFiltersService;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class TicketGridFiltersServiceTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_FILTER_ID = 1;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\TicketGridFiltersService
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\TicketGridFiltersService
     */
    private $ticketGridFiltersService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     */
    private $filterRepository;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     * @Mock \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Filters\FilterUrlGeneratorInterface
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Filters\FilterUrlGeneratorInterface
     */
    private $filterUrlGenerator;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->ticketGridFiltersService = new TicketGridFiltersService($this->container, $this->filterRepository);
    }

    public function testGetFilters()
    {
        $filters = new ArrayCollection();
        $filters->add(new Filter('testFilter', 'testServiceId'));
        $this->filterRepository
            ->expects($this->once())->method('getAll')
            ->will($this->returnValue($filters));

        $returnedFilters = $this->ticketGridFiltersService->getFilters();
        $this->assertCount(1, $returnedFilters);
        $this->assertEquals('testFilter', $filters->get(0)->getName());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Filter loading failed, filter not found.
     */
    public function testGenerateGridFilterUrlThrowsExceptionWhenFilterDoesNotExists()
    {
        $this->filterRepository
            ->expects($this->once())
            ->method('get')->with($this->equalTo(self::DUMMY_FILTER_ID))
            ->will($this->returnValue(null));

        $this->ticketGridFiltersService->generateGridFilterUrl(self::DUMMY_FILTER_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Filter generator loading failed, filter generator not found.
     */
    public function testGenerateGridFilterUrlThrowsExceptionWhenFilterGeneratorDoesNotExists()
    {
        $returnedFilter = new Filter('testFilter', 'testServiceId');

        $this->filterRepository
            ->expects($this->once())
            ->method('get')->with($this->equalTo(self::DUMMY_FILTER_ID))
            ->will($this->returnValue($returnedFilter));

        $this->container
            ->expects($this->once())
            ->method('get')->with($this->equalTo($returnedFilter->getServiceId()))
            ->will($this->returnValue(null));

        $this->ticketGridFiltersService->generateGridFilterUrl(self::DUMMY_FILTER_ID);
    }

    public function testGenerateGridFilterUrl()
    {
        $returnedFilter = new Filter('testFilter', 'testServiceId');

        $this->filterRepository
            ->expects($this->once())
            ->method('get')->with($this->equalTo(self::DUMMY_FILTER_ID))
            ->will($this->returnValue($returnedFilter));

        $this->container
            ->expects($this->once())
            ->method('get')->with($this->equalTo($returnedFilter->getServiceId()))
            ->will($this->returnValue($this->filterUrlGenerator));

        $this->filterUrlGenerator
            ->expects($this->once())
            ->method('generateFilterUrlPart');

        $this->ticketGridFiltersService->generateGridFilterUrl(self::DUMMY_FILTER_ID);
    }
}
