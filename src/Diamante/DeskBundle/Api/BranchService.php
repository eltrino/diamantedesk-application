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

use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Branch\Exception\DuplicateBranchKeyException;

/**
 * Interface BranchService
 * @package Diamante\DeskBundle\Api
 * @codeCoverageIgnore
 */
interface BranchService
{
    /**
     * Retrieves list of all Branches
     * @param Command\Filter\FilterBranchesCommand
     * @return Branch[]
     */
    public function getAllBranches();

    /**
     * Retrieves Branch by id
     * @param $id
     * @return Branch
     */
    public function getBranch($id);

    /**
     * Create Branch
     * @param Command\BranchCommand $branchCommand
     * @return \Diamante\DeskBundle\Model\Branch\Branch
     * @throws DuplicateBranchKeyException
     */
    public function createBranch(Command\BranchCommand $branchCommand);

    /**
     * Update Branch
     * @param Command\BranchCommand $branchCommand
     * @return int
     */
    public function updateBranch(Command\BranchCommand $branchCommand);

    /**
     * Delete Branch
     * @param int $branchId
     * @return void
     */
    public function deleteBranch($branchId);

    /**
     * Update certain properties of the branch
     *
     * @param Command\UpdatePropertiesCommand $command
     */
    public function updateProperties(Command\UpdatePropertiesCommand $command);
}
