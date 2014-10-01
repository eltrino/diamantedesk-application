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

use Eltrino\DiamanteDeskBundle\Branch\Api\Command\EmailProcessing\BranchEmailConfigurationCommand;
use Eltrino\DiamanteDeskBundle\Branch\Model\EmailProcessing\BranchEmailConfiguration;

interface BranchEmailConfigurationService
{
    /**
     * Retrieves BranchEmailConfiguration by Branch Id
     *
     * @param $branchId
     * @return BranchEmailConfiguration
     */
    public function getConfigurationByBranchId($branchId);

    /**
     * Retrieves Branch Id by Support Address and Customer Domain
     *
     * @param $supportAddress
     * @param $customerDomain
     * @return int|null
     */
    public function getConfigurationBySupportAddressAndCustomerDomain($supportAddress, $customerDomain);

    /**
     * Create BranchEmailConfiguration
     * @param BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function createBranchEmailConfiguration(BranchEmailConfigurationCommand $branchEmailConfigurationCommand);

    /**
     * Update BranchEmailConfiguration
     * @param BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function updateBranchEmailConfiguration(BranchEmailConfigurationCommand $branchEmailConfigurationCommand);
} 