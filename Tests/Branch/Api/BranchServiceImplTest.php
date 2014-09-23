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

use Eltrino\DiamanteDeskBundle\Branch\Api\BranchServiceImpl;
use Eltrino\DiamanteDeskBundle\Branch\Api\Command\BranchCommand;
use Eltrino\DiamanteDeskBundle\Branch\Model\Logo;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Oro\Bundle\TagBundle\Entity\TagManager;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class BranchServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BRANCH_ID = 1;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
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

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchServiceImpl = new BranchServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager,
            $this->securityFacade
        );
    }

    /**
     * @test
     */
    public function thatListsAllBranches()
    {
        $branches = array(new Branch('DUMMY_NAME_1', 'DUMMY_DESC_1'), new Branch('DUMMY_NAME_2', 'DUMMY_DESC_2'));
        $this->branchRepository->expects($this->once())->method('getAll')->will($this->returnValue($branches));

        $retrievedBranches = $this->branchServiceImpl->listAllBranches();

        $this->assertNotNull($retrievedBranches);
        $this->assertTrue(is_array($retrievedBranches));
        $this->assertNotEmpty($retrievedBranches);
        $this->assertEquals($branches, $retrievedBranches);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Branch loading failed. Branch not found.
     */
    public function thatRetreivingExceptionsThrowsExceptionIfBranchDoesNotExists()
    {
        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue(null));
        $this->branchServiceImpl->getBranch(100);
    }

    /**
     * @test
     */
    public function thatRetirevesBranchById()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC');
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $retrievedBranch = $this->branchServiceImpl->getBranch(self::DUMMY_BRANCH_ID);

        $this->assertEquals($branch, $retrievedBranch);
    }

    /**
     * @test
     */
    public function createBranchWithOnlyRequiredValues()
    {
        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';
        $branchStub = new Branch($name, $description, null, new Logo('dummy'));

        $this->branchFactory->expects($this->once())->method('create')
            ->with($this->equalTo($name), $this->equalTo($description))->will($this->returnValue($branchStub));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($branchStub));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;

        $this->branchServiceImpl->createBranch($command);
    }

    /**
     * @test
     */
    public function createBranchWithAllValues()
    {
        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';
        $defaultAssignee = new User();
        $tags = array();
        $branch = new Branch($name, $description, null, new Logo('dummy'));

        $this->branchLogoHandler->expects($this->once())->method('upload')->with($this->equalTo($this->fileMock));

        $this->branchFactory->expects($this->once())->method('create')
            ->with(
                $this->equalTo($name), $this->equalTo($description),
                $this->equalTo($defaultAssignee), $this->equalTo($this->fileMock)
            )->will($this->returnValue($branch));

        $this->tagManager->expects($this->once())->method('saveTagging')->with($this->equalTo($branch));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;
        $command->defaultAssignee = $defaultAssignee;
        $command->tags = $tags;
        $command->logoFile = $this->fileMock;

        $this->branchServiceImpl->createBranch($command);
    }

    /**
     * @test
     */
    public function updateBranchWithOnlyRequiredValues()
    {
        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue($this->branch));
        $this->branch->expects($this->never())->method('getLogo');
        $this->branchLogoHandler->expects($this->never())->method('remove');
        $this->branchLogoHandler->expects($this->never())->method('upload');

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';

        $this->branch->expects($this->once())->method('update')
            ->with($this->equalTo($name), $this->equalTo($description));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($this->branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;

        $this->branchServiceImpl->updateBranch($command);
    }

    /**
     * @test
     */
    public function updateBranchWithAllValues()
    {
        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue($this->branch));
        $this->branch->expects($this->exactly(2))->method('getLogo')->will($this->returnValue($this->logo));
        $this->branchLogoHandler->expects($this->once())->method('remove')->with($this->equalTo($this->logo));
        $this->branchLogoHandler->expects($this->once())->method('upload')->with($this->equalTo($this->fileMock))
            ->will($this->returnValue($this->fileMock));

        $this->logo->expects($this->once())->method('getFilename');

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';
        $defaultAssignee = new User();
        $tags = array();

        $this->branch->expects($this->once())->method('update')->with(
            $this->equalTo($name), $this->equalTo($description), $this->equalTo($defaultAssignee),
            $this->equalTo($this->fileMock)
        );

        $this->branch->expects($this->once())->method('setTags')->with($this->equalTo($tags));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($this->branch));

        $this->tagManager->expects($this->once())->method('saveTagging')->with($this->equalTo($this->branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;
        $command->defaultAssignee = $defaultAssignee;
        $command->logoFile = $this->fileMock;
        $command->tags = $tags;

        $this->branchServiceImpl->updateBranch($command);
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

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithLogo()
    {
        $branch = new Branch('DUMMY_NAME', 'DUMMY_DESC', null, new Logo('dummy'));

        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->once())->method('remove');

        $this->branchRepository->expects($this->once())->method('remove')->with($this->equalTo($branch));

        $this->securityFacade->expects($this->once())->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

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

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }
}
