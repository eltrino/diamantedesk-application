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
namespace Diamante\DeskBundle\Tests\Api\Internal;

use Diamante\DeskBundle\Api\Command\BranchEmailConfigurationCommand;
use Diamante\DeskBundle\Api\Internal\BranchEmailConfigurationServiceImpl;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class BranchEmailConfigurationServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BRANCH_ID       = 1;
    const DUMMY_SUPPORT_ADDRESS = 'dummy_support_address';
    const DUMMY_CUSTOMER_DOMAIN = 'dummy_customer_domain';
    const DUMMY_CUSTOMER_DOMAINS = 'gmail.com, yahoo.com';

    /**
     * @var \Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationRepository
     * @Mock \Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationRepository
     */
    private $branchEmailConfigurationRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationFactory
     * @Mock \Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationFactory
     */
    private $branchEmailConfigurationFactory;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $branchRepository;

    /**
     * @var BranchEmailConfigurationServiceImpl
     */
    private $branchEmailConfigurationServiceImpl;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\Branch
     * @Mock \Diamante\DeskBundle\Model\Branch\Branch
     */
    private $branch;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration
     * @Mock \Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration
     */
    private $branchEmailConfiguration;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     * @Mock Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     * @Mock Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrineRegistry;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchEmailConfigurationServiceImpl = new BranchEmailConfigurationServiceImpl(
            $this->doctrineRegistry,
            $this->branchEmailConfigurationFactory,
            $this->branchEmailConfigurationRepository,
            $this->branchRepository,
            $this->logger
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

        $this->doctrineRegistry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));

        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->equalTo($branchEmailConfigurationStub));

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

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

        $this->doctrineRegistry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));


        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->equalTo($this->branchEmailConfiguration));

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

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

        $this->doctrineRegistry
            ->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->entityManager));


        $this->entityManager->expects($this->once())->method('persist')
            ->with($this->equalTo($branchEmailConfigurationStub));

        $this->entityManager
            ->expects($this->never())
            ->method('flush');

        $command = new BranchEmailConfigurationCommand();
        $command->branch = self::DUMMY_BRANCH_ID;
        $command->customerDomains = self::DUMMY_CUSTOMER_DOMAINS;
        $command->supportAddress  = self::DUMMY_SUPPORT_ADDRESS;

        $this->branchEmailConfigurationServiceImpl->updateBranchEmailConfiguration($command);
    }

    /**
     * @test
     */
    public function testBranchDefaultAssigneeRetrieved()
    {
        $this->branchRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->branch));

        $this->branch
            ->expects($this->once())
            ->method('getDefaultAssigneeId')
            ->will($this->returnValue(1));

        $assignee = $this->branchEmailConfigurationServiceImpl->getBranchDefaultAssignee(1);

        $this->assertEquals(1, $assignee);
    }
}
