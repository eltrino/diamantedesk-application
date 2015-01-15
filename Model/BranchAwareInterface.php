<?php

namespace Diamante\DiamanteDeskBundle\Model;

use Diamante\DeskBundle\Entity\Branch;

interface BranchAwareInterface
{
    /**
     * @param Branch $branch
     *
     */
    public function setBranch(Branch $branch);

    /**
     * @return Branch
     */
    public function getBranch();
}