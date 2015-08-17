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
namespace Diamante\DeskBundle\Tests\Model\Branch;

use Diamante\DeskBundle\Model\Branch\BranchFactory;
use Diamante\DeskBundle\Model\Branch\Logo;
use Diamante\DeskBundle\Tests\Stubs\FileInfoStub;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class BranchFactoryTest extends \PHPUnit_Framework_TestCase
{
    const KEY = 'DB';
    const NAME = 'Diamante Branch';
    const DESCRIPTION = 'Description';
    const FILE_NAME = 'file.ext';

    /**
     * @var \Diamante\DeskBundle\Model\Branch\BranchKeyGenerator
     * @Mock \Diamante\DeskBundle\Model\Branch\BranchKeyGenerator
     */
    private $branchKeyGenerator;

    /**
     * @var BranchFactory
     */
    private $factory;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->factory = new BranchFactory(
            '\Diamante\DeskBundle\Model\Branch\Branch', $this->branchKeyGenerator
        );
    }

    public function testCreateWhenLogoIsDefined()
    {
        $this->branchKeyGenerator->expects($this->exactly(0))->method('generate');
        $logo = new Logo(self::FILE_NAME, self::FILE_NAME);

        $branch = $this->factory->create(self::NAME, self::DESCRIPTION, self::KEY, new User(), $logo);

        $this->assertEquals(self::KEY, $branch->getKey());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Branch\Logo', $branch->getLogo());
        $this->assertEquals(self::FILE_NAME, $branch->getLogo()->getName());
    }

    public function testCreateWhenKeyIsNotDefined()
    {
        $this->branchKeyGenerator->expects($this->once())->method('generate')->with(self::NAME)
            ->will($this->returnValue(self::KEY));

        $branch = $this->factory->create(self::NAME, self::DESCRIPTION);

        $this->assertEquals(self::KEY, $branch->getKey());
    }
}
