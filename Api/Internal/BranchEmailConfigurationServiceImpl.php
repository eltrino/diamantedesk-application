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
use Doctrine\ORM\EntityManager;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationFactory;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfigurationRepository;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;
use Diamante\DeskBundle\Api\Command;

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

    public function __construct(
        BranchEmailConfigurationFactory $branchEmailConfigurationFactory,
        BranchEmailConfigurationRepository $branchEmailConfigurationRepository,
        Repository $branchRepository
    ) {
        $this->branchEmailConfigurationFactory    = $branchEmailConfigurationFactory;
        $this->branchEmailConfigurationRepository = $branchEmailConfigurationRepository;
        $this->branchRepository                   = $branchRepository;
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
     */
    public function createBranchEmailConfiguration(Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand)
    {
        $branch = $this->branchRepository->get($branchEmailConfigurationCommand->branch);

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
}
