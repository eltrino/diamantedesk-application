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
namespace Diamante\DeskBundle\Api;

use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;

/**
 * Interface BranchEmailConfigurationService
 * @package Diamante\DeskBundle\Api
 * @codeCoverageIgnore
 */
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
     * @param Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function createBranchEmailConfiguration(Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand);

    /**
     * Update BranchEmailConfiguration
     * @param Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand
     * @return int
     */
    public function updateBranchEmailConfiguration(Command\BranchEmailConfigurationCommand $branchEmailConfigurationCommand);
}
