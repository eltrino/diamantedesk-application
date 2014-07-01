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
namespace Eltrino\DiamanteDeskBundle\Tests\Entity;

use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;

class BranchTest extends \PHPUnit_Framework_TestCase
{
    const BRANCH_NAME         = 'Name';
    const BRANCH_DESCRIPTION  = 'Description';
    const BRANCH_IMAGE        = 'Image';

    /**
     * @test
     */
    public function thatCreate()
    {
        $logo = new Logo('file.dummy');
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC', $logo);

        $this->assertEquals('DUMMY_NAME', $branch->getName());
        $this->assertEquals('DUMMY_DESC', $branch->getDescription());
        $this->assertEquals($logo, $branch->getLogo());
    }

    /**
     * @test
     */
    public function thatUpdate()
    {
        $logo = new Logo('file.dummy');
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC', $logo);

        $newLogo = new Logo('new_file.dummy');
        $branch->update('New Name', 'New Description', $newLogo);

        $this->assertEquals('New Name', $branch->getName());
        $this->assertEquals('New Description', $branch->getDescription());
        $this->assertEquals($newLogo, $branch->getLogo());
    }

}
