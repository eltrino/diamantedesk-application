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
namespace Eltrino\DiamanteDeskBundle\Tests\Branch\Api;

use Eltrino\DiamanteDeskBundle\Form\Command\BranchCommand;
use Eltrino\DiamanteDeskBundle\Branch\Api\BranchServiceImpl;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class BranchServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BRANCH_ID = 1;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
     */
    private $branchRepository;

    /**
     * @var BranchServiceImpl
     */
    private $branchServiceImpl;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\Factory\BranchFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\Factory\BranchFactory
     */
    private $branchFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Infrastructure\BranchLogoHandler
     */
    private $branchLogoHandler;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     * @Mock \Oro\Bundle\TagBundle\Entity\TagManager
     */
    private $tagManager;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * @Mock \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $fileMock;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\Logo
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\Logo
     */
    private $logo;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Branch
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\Branch
     */
    private $branch;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchServiceImpl = new BranchServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager
        );
    }

    /**
     * @test
     */
    public function createBranchWithLogo()
    {
        $branchStub = new Branch('DUMMY_NAME', 'DUMMY_DESC', new Logo('dummy'));

        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';
        $logoFile = $this->fileMock;

//        $this->branchLogoHandler
//            ->expects($this->once())
//            ->method('upload')
//            ->with($this->equalTo($this->fileMock))
//            ->will($this->returnValue($this->fileMock));

        $this->branchFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo('DUMMY_NAME'), $this->equalTo('DUMMY_DESC'), $this->equalTo($this->fileMock))
            ->will($this->returnValue($branchStub));

        $this->branchRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->equalTo($branchStub));

        $this->tagManager
            ->expects($this->once())
            ->method('saveTagging');

        $this->branchServiceImpl->createBranch($name, $description, $logoFile);
    }

    /**
     * @test
     */
    public function createBranchWithoutLogo()
    {
        $branchStub = new Branch('DUMMY_NAME', 'DUMMY_DESC');

        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';

        $this->branchLogoHandler
            ->expects($this->never())
            ->method('upload');

        $this->branchFactory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo('DUMMY_NAME'), $this->equalTo('DUMMY_DESC'))
            ->will($this->returnValue($branchStub));

        $this->branchRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->equalTo($branchStub));

        $this->tagManager
            ->expects($this->once())
            ->method('saveTagging');

        $this->branchServiceImpl->createBranch($name, $description);
    }

    /**
     * @test
     */
    public function updateBranchWithLogo()
    {
        $this->branchRepository->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->branch));

//        $this->branch->expects($this->exactly(2))
//            ->method('getLogo')
//            ->will($this->returnValue($this->logo));

//        $this->branchLogoHandler->expects($this->once())
//            ->method('remove')
//            ->with($this->equalTo($this->logo));

//        $this->branchLogoHandler
//            ->expects($this->once())
//            ->method('upload')
//            ->with($this->equalTo($this->fileMock))
//            ->will($this->returnValue($this->fileMock));

//        $this->fileMock->expects($this->once())
//            ->method('getFilename');

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';
        $logoFile = $this->fileMock;

        $this->branch->expects($this->once())
            ->method('update')
            ->with($this->equalTo('DUMMY_NAME_UPDT'), $this->equalTo('DUMMY_DESC_UPDT'));

        $this->branch
            ->expects($this->once())
            ->method('setTags');

        $this->branchRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->equalTo($this->branch));

        $this->tagManager
            ->expects($this->once())
            ->method('saveTagging');

        $this->branchServiceImpl->updateBranch(self::DUMMY_BRANCH_ID, $name, $description, $logoFile);
    }

    /**
     * @test
     */
    public function updateBranchWithoutLogo()
    {
        $this->branchRepository->expects($this->once())
            ->method('get')
            ->will($this->returnValue($this->branch));

        $this->branch->expects($this->never())
            ->method('getLogo');

//        $this->branchLogoHandler->expects($this->never())
//            ->method('remove');

        $this->branchLogoHandler
            ->expects($this->never())
            ->method('upload');

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';

        $this->branch->expects($this->once())
            ->method('update')
            ->with($this->equalTo('DUMMY_NAME_UPDT'), $this->equalTo('DUMMY_DESC_UPDT'));

        $this->branch
            ->expects($this->once())
            ->method('setTags');

        $this->branchRepository
            ->expects($this->once())
            ->method('store')
            ->with($this->equalTo($this->branch));

        $this->tagManager
            ->expects($this->once())
            ->method('saveTagging');

        $this->branchServiceImpl->updateBranch(self::DUMMY_BRANCH_ID, $name, $description);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Branch loading failed, branch not found.
     */
    public function thatBranchDeleteThrowsExceptionIfBranchDoesNotExists()
    {
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue(null));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithLogo()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC', new Logo('dummy'));

        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->once())
            ->method('remove');

        $this->branchRepository->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($branch));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithoutLogo()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC');

        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->never())
            ->method('remove');

        $this->branchRepository->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($branch));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }
}
