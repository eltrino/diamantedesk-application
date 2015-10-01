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

use Diamante\DeskBundle\Api\BranchEmailConfigurationService;
use Diamante\DeskBundle\Model\Shared\Repository;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationFactory;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationRepository;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;
use Diamante\DeskBundle\Api\Command;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Monolog\Logger;

class BranchEmailConfigurationServiceImpl implements BranchEmailConfigurationService
{
    /**
     * @var BranchEmailConfigurationRepository
     */
    private $branchEmailConfigurationRepository;

    /**
     * @var Repository
     */
    private $branchRepository;

    /**
     * @var BranchEmailConfigurationFactory
     */
    private $branchEmailConfigurationFactory;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    private $doctrineRegistry;

    public function __construct(
        Registry $doctrineRegistry,
        BranchEmailConfigurationFactory $branchEmailConfigurationFactory,
        BranchEmailConfigurationRepository $branchEmailConfigurationRepository,
        Repository $branchRepository,
        Logger  $logger
    ) {
        $this->branchEmailConfigurationFactory    = $branchEmailConfigurationFactory;
        $this->branchEmailConfigurationRepository = $branchEmailConfigurationRepository;
        $this->branchRepository                   = $branchRepository;
        $this->logger                             = $logger;
        $this->doctrineRegistry                   = $doctrineRegistry;
    }

    /**
     * Retrieves BranchEmailConfiguration by Branch Id
     *
     * @param $branchId
     * @return BranchEmailConfiguration
     */
    public function getConfigurationByBranchId($branchId)
    {
        $branchEmailConfiguration = $this->branchEmailConfigurationRepository->getByBranchId($branchId);

        return $branchEmailConfiguration;
    }

    /**
     * Retrieves Branch Id by Support Address and Customer Domain
     *
     * @param $supportAddress
     * @param $customerDomain
     * @return int|null
     */
    public function getConfigurationBySupportAddressAndCustomerDomain($supportAddress, $customerDomain)
    {
        $branchId = $this->branchEmailConfigurationRepository
            ->getBySupportAddressAndCustomerDomainCriteria($supportAddress, $customerDomain);

        return $branchId;
    }

    /**
     * Create BranchEmailConfiguration
     * @param Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     * @throws \RuntimeException if unable to load required branch
     */
    public function createBranchEmailConfiguration(Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand)
    {
        $branch = $this->branchRepository->get($branchEmailConfigurationCommand->branch);

        if (is_null($branch)) {
            $this->logger->error(sprintf('Failed to load email configuration for branch: %s',$branchEmailConfigurationCommand->branch));
            throw new \RuntimeException('Branch Email Configuration loading failed, branch not found.');
        }

        $branchEmailConfiguration = $this->branchEmailConfigurationFactory
            ->create(
                $branch,
                $branchEmailConfigurationCommand->customerDomains,
                $branchEmailConfigurationCommand->supportAddress
            );

        $this->branchEmailConfigurationRepository->store($branchEmailConfiguration);

        return $branchEmailConfiguration->getId();
    }

    /**
     * Update BranchEmailConfiguration
     *
     * @param Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function updateBranchEmailConfiguration(Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand)
    {
        if ($this->getConfigurationByBranchId($branchEmailConfigurationCommand->branch))
        {
            $branchEmailConfiguration = $this->getConfigurationByBranchId($branchEmailConfigurationCommand->branch);
            $branchEmailConfiguration->update(
                $branchEmailConfigurationCommand->customerDomains,
                $branchEmailConfigurationCommand->supportAddress
            );
        } else {
            $branch = $this->branchRepository->get($branchEmailConfigurationCommand->branch);
            $branchEmailConfiguration = $this->branchEmailConfigurationFactory
                ->create(
                    $branch,
                    $branchEmailConfigurationCommand->customerDomains,
                    $branchEmailConfigurationCommand->supportAddress
                );
        }

        $this->branchEmailConfigurationRepository->store($branchEmailConfiguration);
        return $branchEmailConfiguration->getId();
    }

    /**
     * @param $branchId
     * @return int
     */
    public function getBranchDefaultAssignee($branchId)
    {
        /**
         * @var $branch \Diamante\DeskBundle\Model\Branch\Branch
         */
        $branch = $this->branchRepository->get($branchId);

        if (empty($branch)) {
            throw new \RuntimeException('No branch with given ID found');
        }

        return $branch->getDefaultAssigneeId();
    }
}
