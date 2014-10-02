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
namespace Diamante\DeskBundle\Tests\Model\Branch\EmailProcessing;

use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;
use Diamante\DeskBundle\Model\Branch\Branch;

class BranchEmailConfigurationTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_SUPPORT_ADDRESS = 'dummy_support_address';
    const DUMMY_CUSTOMER_DOMAINS = 'gmail.com, yahoo.com';

    /**
     * @test
     */
    public function thatCreate()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC');

        $branchEmailConfiguration = new BranchEmailConfiguration(
            $branch, self::DUMMY_CUSTOMER_DOMAINS, self::DUMMY_SUPPORT_ADDRESS
        );

        $this->assertEquals($branch, $branchEmailConfiguration->getBranch());
        $this->assertEquals(self::DUMMY_CUSTOMER_DOMAINS, $branchEmailConfiguration->getCustomerDomains());
        $this->assertEquals(self::DUMMY_SUPPORT_ADDRESS, $branchEmailConfiguration->getSupportAddress());
    }

    /**
     * @test
     */
    public function thatUpdate()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC');

        $branchEmailConfiguration = new BranchEmailConfiguration(
            $branch, self::DUMMY_CUSTOMER_DOMAINS, self::DUMMY_SUPPORT_ADDRESS
        );

        $branchEmailConfiguration->update('New Customer Domains', 'New Support Address');

        $this->assertEquals('New Customer Domains', $branchEmailConfiguration->getCustomerDomains());
        $this->assertEquals('New Support Address', $branchEmailConfiguration->getSupportAddress());
    }

}
