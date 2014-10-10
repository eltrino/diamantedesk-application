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
namespace Diamante\DeskBundle\Api\Command;

use Symfony\Component\Validator\Constraints as Assert;
use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;

class BranchEmailConfigurationCommand
{
    /**
     * @Assert\Type(type="integer")
     */
    public $id;

    /**
     * @Assert\Type(type="object")
     */
    public $branch;

    /**
     * @Assert\Type(type="string")
     */
    public $customerDomains;
    /**
     * @Assert\Type(type="string")
     */
    public $supportAddress;

    public static function fromBranchEmailConfiguration(BranchEmailConfiguration $branchEmailConfiguration)
    {
        $command                       = new self();
        $command->id                   = $branchEmailConfiguration->getId();
        $command->branch               = $branchEmailConfiguration->getBranch();
        $command->customerDomains      = $branchEmailConfiguration->getCustomerDomains();
        $command->supportAddress       = $branchEmailConfiguration->getSupportAddress();

        return $command;
    }
}
