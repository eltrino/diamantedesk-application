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
namespace Diamante\DeskBundle\Infrastructure\Branch;

use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;

/**
 * Class AutocompleteBranchServiceImpl
 *
 * @package Diamante\DeskBundle\Infrastructure\Branch
 */
class AutocompleteBranchServiceImpl implements AutocompleteBranchService
{
    /**
     * @var DoctrineGenericRepository
     */
    protected $branchRepository;

    /**
     * AutocompleteBranchServiceImpl constructor.
     *
     * @param DoctrineGenericRepository $branchRepository
     */
    public function __construct(
        DoctrineGenericRepository $branchRepository
    ) {
        $this->branchRepository = $branchRepository;
    }

    /**
     * @return array
     */
    public function getBranches()
    {
        $branches = $this->branchRepository->getAll();
        $orderedBranches = [];

        /** @var Branch $branch */
        foreach ($branches as $branch) {
            $orderedBranches[$branch->getId()] = $branch->getName();
        }

        return $orderedBranches;
    }
}
