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

namespace Eltrino\DiamanteDeskBundle\Tests\Branch\Api\EmailProcessing;

use Eltrino\DiamanteDeskBundle\Branch\Api\Command\EmailProcessing\BranchEmailConfigurationCommand;
use Eltrino\DiamanteDeskBundle\Branch\Api\EmailProcessing\BranchEmailConfigurationServiceImpl;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Entity\BranchEmailConfiguration;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class BranchEmailConfigurationServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BRANCH_ID       = 1;
    const DUMMY_SUPPORT_ADDRESS = 'dummy_support_address';
    const DUMMY_CUSTOMER_DOMAIN = 'dummy_customer_domain';
    const DUMMY_CUSTOMER_DOMAINS = 'gmail.com, yahoo.com';

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\EmailProcessing\BranchEmailConfigurationRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\EmailProcessing\BranchEmailConfigurationRepository
     */
    private $branchEmailConfigurationRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\Factory\EmailProcessing\BranchEmailConfigurationFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\Factory\EmailProcessing\BranchEmailConfigurationFactory
     */
    private $branchEmailConfigurationFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
     */
    private $branchRepository;

    /**
     * @var BranchEmailConfigurationServiceImpl
     */
    private $branchEmailConfigurationServiceImpl;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Branch
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\Branch
     */
    private $branch;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\BranchEmailConfiguration
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\BranchEmailConfiguration
     */
    private $branchEmailConfiguration;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchEmailConfigurationServiceImpl = new BranchEmailConfigurationServiceImpl(
            $this->branchEmailConfigurationFactory,
            $this->branchEmailConfigurationRepository,
            $this->branchRepository
        );
    }

    /**
     * @test
     */
    public function thatRetirevesBranchById()
    {
        $this->branchEmailConfigurationRepository->expects($this->once())->method('getByBranchId')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($this->branchEmailConfiguration));

        $retrievedConfiguration = $this->branchEmailConfigurationServiceImpl->getConfigurationByBranchId(self::DUMMY_BRANCH_ID);

        $this->assertEquals($this->branchEmailConfiguration, $retrievedConfiguration);
    }

    /**
     * @test
     */
    public function thatRetirevesConfigurationBySupportAddressAndCustomerDomain()
    {
        $this->branchEmailConfigurationRepository->expects($this->once())
            ->method('getBySupportAddressAndCustomerDomainCriteria')
            ->with(
                $this->equalTo(self::DUMMY_SUPPORT_ADDRESS),
                $this->equalTo(self::DUMMY_CUSTOMER_DOMAIN)
            )
            ->will($this->returnValue(self::DUMMY_BRANCH_ID));

        $branchId = $this->branchEmailConfigurationServiceImpl
            ->getConfigurationBySupportAddressAndCustomerDomain(self::DUMMY_SUPPORT_ADDRESS,
                self::DUMMY_CUSTOMER_DOMAIN);

        $this->assertEquals(self::DUMMY_BRANCH_ID, $branchId);
    }

    /**
     * @test
     */
    public function createBranchEmailConfiguration()
    {
        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($this->branch));

        $branchEmailConfigurationStub = new BranchEmailConfiguration($this->branch, self::DUMMY_CUSTOMER_DOMAINS,
            self::DUMMY_SUPPORT_ADDRESS);

        $this->branchEmailConfigurationFactory
            ->expects($this->once())->method('create')
            ->with(
                $this->equalTo($this->branch),
                $this->equalTo(self::DUMMY_CUSTOMER_DOMAINS),
                $this->equalTo(self::DUMMY_SUPPORT_ADDRESS)
            )->will($this->returnValue($branchEmailConfigurationStub));

        $this->branchEmailConfigurationRepository->expects($this->once())->method('store')
            ->with($this->equalTo($branchEmailConfigurationStub));

        $command = new BranchEmailConfigurationCommand();
        $command->branch          = self::DUMMY_BRANCH_ID;
        $command->customerDomains = self::DUMMY_CUSTOMER_DOMAINS;
        $command->supportAddress  = self::DUMMY_SUPPORT_ADDRESS;
        $this->branchEmailConfigurationServiceImpl->createBranchEmailConfiguration($command);
    }

    /**
     * @test
     */
    public function updateBranchEmailConfigurationWhenExists()
    {

        $this->branchEmailConfigurationRepository->expects($this->exactly(2))->method('getByBranchId')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($this->branchEmailConfiguration));

        $this->branchEmailConfiguration->expects($this->once())
            ->method('update')
            ->with(
                $this->equalTo(self::DUMMY_CUSTOMER_DOMAINS),
                $this->equalTo(self::DUMMY_SUPPORT_ADDRESS)
            )
            ->will($this->returnValue($this->branchEmailConfiguration));


        $this->branchEmailConfigurationRepository->expects($this->once())->method('store')
            ->with($this->equalTo($this->branchEmailConfiguration));

        $command = new BranchEmailConfigurationCommand();
        $command->branch          = self::DUMMY_BRANCH_ID;
        $command->customerDomains = self::DUMMY_CUSTOMER_DOMAINS;
        $command->supportAddress  = self::DUMMY_SUPPORT_ADDRESS;
        $this->branchEmailConfigurationServiceImpl->updateBranchEmailConfiguration($command);
    }

    /**
     * @test
     */
    public function updateBranchEmailConfigurationWhenNotExists()
    {
        $this->branchEmailConfigurationRepository->expects($this->exactly(1))->method('getByBranchId')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue(null));

        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($this->branch));

        $branchEmailConfigurationStub = new BranchEmailConfiguration($this->branch, self::DUMMY_CUSTOMER_DOMAINS,
            self::DUMMY_SUPPORT_ADDRESS);

        $this->branchEmailConfigurationFactory
            ->expects($this->once())->method('create')
            ->with(
                $this->equalTo($this->branch),
                $this->equalTo(self::DUMMY_CUSTOMER_DOMAINS),
                $this->equalTo(self::DUMMY_SUPPORT_ADDRESS)
            )->will($this->returnValue($branchEmailConfigurationStub));

        $this->branchEmailConfigurationRepository->expects($this->once())->method('store')
            ->with($this->equalTo($branchEmailConfigurationStub));

        $command = new BranchEmailConfigurationCommand();
        $command->branch = self::DUMMY_BRANCH_ID;
        $command->customerDomains = self::DUMMY_CUSTOMER_DOMAINS;
        $command->supportAddress  = self::DUMMY_SUPPORT_ADDRESS;

        $this->branchEmailConfigurationServiceImpl->updateBranchEmailConfiguration($command);
    }
} 