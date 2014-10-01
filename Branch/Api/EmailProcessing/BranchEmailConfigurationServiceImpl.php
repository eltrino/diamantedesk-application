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

namespace Eltrino\DiamanteDeskBundle\Branch\Api\EmailProcessing;

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Branch\Model\Factory\EmailProcessing\BranchEmailConfigurationFactory;
use Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository;
use Eltrino\DiamanteDeskBundle\Branch\Model\EmailProcessing\BranchEmailConfigurationRepository;
use Eltrino\DiamanteDeskBundle\Entity\BranchEmailConfiguration;
use Eltrino\DiamanteDeskBundle\Branch\Api\Command\EmailProcessing\BranchEmailConfigurationCommand;

class BranchEmailConfigurationServiceImpl implements BranchEmailConfigurationService
{
    /**
     * @var BranchEmailConfigurationRepository
     */
    private $branchEmailConfigurationRepository;

    /**
     * @var BranchRepository
     */
    private $branchRepository;

    /**
     * @var BranchEmailConfigurationFactory
     */
    private $branchEmailConfigurationFactory;

    public function __construct(
        BranchEmailConfigurationFactory $branchEmailConfigurationFactory,
        BranchEmailConfigurationRepository $branchEmailConfigurationRepository,
        BranchRepository $branchRepository
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
     * @param BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function createBranchEmailConfiguration(BranchEmailConfigurationCommand $branchEmailConfigurationCommand)
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
     * @param BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function updateBranchEmailConfiguration(BranchEmailConfigurationCommand $branchEmailConfigurationCommand)
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
     * @param BranchEmailConfigurationFactory $branchEmailConfigurationFactory
     * @param EntityManager $em
     * @return BranchEmailConfigurationServiceImpl
     */
    public static function create(
        BranchEmailConfigurationFactory $branchEmailConfigurationFactory,
        EntityManager $em
    )
    {
        return new BranchEmailConfigurationServiceImpl(
            $branchEmailConfigurationFactory,
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\BranchEmailConfiguration'),
            $em->getRepository('Eltrino\DiamanteDeskBundle\Entity\Branch')
        );
    }
} 