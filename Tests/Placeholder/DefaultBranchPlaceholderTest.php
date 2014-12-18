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
namespace Diamante\DeskBundle\Tests\Placeholder;

use Diamante\DeskBundle\Placeholder\DefaultBranchPlaceholder;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class DefaultBranchPlaceholderTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_BRANCH_ID = 8;

    /**
     * @var DefaultBranchPlaceholder
     */
    private $placeholder;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $systemSettings;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->placeholder = new DefaultBranchPlaceholder($this->systemSettings);
    }

    /**
     * @test
     */
    public function testIsDefault()
    {
        $branches = array(
            new Branch(8, 'DUMMY_NAME', "DUMMY_DESC"),
            new Branch(9, 'DUMMY_NAME', "DUMMY_DESC")
        );

        $this->systemSettings
            ->expects($this->any())
            ->method('getDefaultBranchId')
            ->will($this->returnValue(self::DEFAULT_BRANCH_ID));

        $result = $this->placeholder->isDefault($branches[0]);
        $this->assertTrue($result);

        $result = $this->placeholder->isDefault($branches[1]);
        $this->assertFalse($result);
    }
}
