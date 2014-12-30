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
namespace Diamante\DeskBundle\Tests\Api\Internal;

use Diamante\DeskBundle\Api\Command\UpdatePropertiesCommand;
use Diamante\DeskBundle\Api\Internal\BranchServiceImpl;
use Diamante\DeskBundle\Api\Command\BranchCommand;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Diamante\DeskBundle\Model\Branch\Logo;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Tests\Stubs\UploadedFileStub;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\Shared\UserService;
use Diamante\DeskBundle\Model\User\User;

class BranchServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_BRANCH_ID = 1;
    const DUMMY_LOGO_PATH = 'uploads/branch/logo';
    const DUMMY_LOGO_NAME = 'dummy-logo-name.png';

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $branchRepository;

    /**
     * @var BranchServiceImpl
     */
    private $branchServiceImpl;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\BranchFactory
     * @Mock \Diamante\DeskBundle\Model\Branch\BranchFactory
     */
    private $branchFactory;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler
     * @Mock \Diamante\DeskBundle\Infrastructure\Branch\BranchLogoHandler
     */
    private $branchLogoHandler;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     * @Mock \Oro\Bundle\TagBundle\Entity\TagManager
     */
    private $tagManager;

    /**
     * @var \Diamante\DeskBundle\Tests\Stubs\UploadedFileStub
     */
    private $fileMock;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\Logo
     * @Mock \Diamante\DeskBundle\Model\Branch\Logo
     */
    private $logo;

    /**
     * @var \Diamante\DeskBundle\Model\Branch\Branch
     * @Mock \Diamante\DeskBundle\Model\Branch\Branch
     */
    private $branch;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService
     * @Mock \Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService
     */
    private $authorizationService;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     * @Mock \Doctrine\ORM\UnitOfWork
     */
    private $unitOfWork;


    /**
     * @var \Doctrine\ORM\Persisters\BasicEntityPersister
     * @Mock \Doctrine\ORM\Persisters\BasicEntityPersister
     */
    private $entityPersister;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\UserService
     * @Mock Diamante\DeskBundle\Model\Shared\UserService
     */
    private $userService;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchServiceImpl = new BranchServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager,
            $this->authorizationService,
            $this->userService
        );
    }

    /**
     * @test
     */
    public function thatListsAllBranches()
    {
        $branches = array(
            new Branch('DUMM', 'DUMMY_NAME_1', 'DUMMY_DESC_1'),
            new Branch('DUMMY', 'DUMMY_NAME_2', 'DUMMY_DESC_2')
        );
        $this->branchRepository->expects($this->once())->method('getAll')->will($this->returnValue($branches));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('VIEW'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

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
        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('VIEW'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue(null));
        $this->branchServiceImpl->getBranch(100);
    }

    /**
     * @test
     */
    public function thatRetrievesBranchById()
    {
        $branch = new Branch('DN', 'DUMMY_NAME', 'DUMMY_DESC');
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('VIEW'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

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
        $branchStub = new Branch('DUMMY', $name, $description, null, new Logo('dummy'));

        $this->branchFactory->expects($this->once())->method('create')
            ->with($this->equalTo($name), $this->equalTo($description))->will($this->returnValue($branchStub));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($branchStub));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
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
        $key = 'DB';
        $name = 'DUMMY_NAME';
        $description = 'DUMMY_DESC';
        $assigneeId = 1;
        $assignee = new User($assigneeId, User::TYPE_ORO);
        $defaultAssignee = new OroUser();
        $tags = array();
        $branch = new Branch($key, $name, $description, null, new Logo('dummy'));
        $this->fileMock = new UploadedFileStub(self::DUMMY_LOGO_PATH, self::DUMMY_LOGO_NAME);

        $this->userService
            ->expects($this->once())
            ->method('getByUser')
            ->with($this->equalTo($assignee))
            ->will($this->returnValue($defaultAssignee));

        $this->branchLogoHandler
            ->expects($this->once())
            ->method('upload')
            ->with($this->equalTo($this->fileMock))
            ->will($this->returnValue($this->fileMock));

        $this->branchFactory->expects($this->once())->method('create')
            ->with(
                $this->equalTo($name), $this->equalTo($description), $key,
                $this->equalTo($defaultAssignee), $this->equalTo($this->fileMock)
            )->will($this->returnValue($branch));

        $this->tagManager->expects($this->once())->method('saveTagging')->with($this->equalTo($branch));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($branch));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new BranchCommand();
        $command->key = $key;
        $command->name = $name;
        $command->description = $description;
        $command->defaultAssignee = $assigneeId;
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

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
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
        $this->fileMock = new UploadedFileStub(self::DUMMY_LOGO_PATH, self::DUMMY_LOGO_NAME);
        $uploadedFile = $this->fileMock->move(self::DUMMY_LOGO_PATH, self::DUMMY_LOGO_NAME);

        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue($this->branch));
        $this->branch->expects($this->exactly(2))->method('getLogo')->will($this->returnValue($this->logo));
        $this->branchLogoHandler->expects($this->once())->method('remove')->with($this->equalTo($this->logo));
        $this->branchLogoHandler->expects($this->once())->method('upload')->with($this->equalTo($this->fileMock))
            ->will($this->returnValue($uploadedFile));

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';
        $assigneeId = 1;
        $assignee = new User($assigneeId, User::TYPE_ORO);
        $defaultAssignee = new OroUser();
        $tags = array(
            'autocomplete' => array(),
            'all'          => array(),
            'owner'        => array()
        );

        $this->branch->expects($this->once())->method('update')->with(
            $this->equalTo($name), $this->equalTo($description), $this->equalTo($defaultAssignee),
            $this->equalTo(new Logo($uploadedFile->getFilename()))
        );

        $this->branch->expects($this->once())->method('setTags')->with($this->equalTo($tags));

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($this->branch));

        $this->tagManager->expects($this->once())->method('saveTagging')->with($this->equalTo($this->branch));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->userService
            ->expects($this->once())
            ->method('getbyUser')
            ->with($this->equalTo($assignee))
            ->will($this->returnValue($defaultAssignee));

        $command = new BranchCommand();
        $command->name = $name;
        $command->description = $description;
        $command->defaultAssignee = $assigneeId;
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

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithLogo()
    {
        $branch = new Branch('DUMM', 'DUMMY_NAME', 'DUMMY_DESC', null, new Logo('dummy'));

        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->once())->method('remove');

        $this->branchRepository->expects($this->once())->method('remove')->with($this->equalTo($branch));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    /**
     * @test
     */
    public function testDeleteBranchWithoutLogo()
    {
        $branch = new Branch('DUMM', 'DUMMY_NAME', 'DUMMY_DESC');

        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo(self::DUMMY_BRANCH_ID))
            ->will($this->returnValue($branch));

        $this->branchLogoHandler->expects($this->never())
            ->method('remove');

        $this->branchRepository->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($branch));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('DELETE'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $this->branchServiceImpl->deleteBranch(self::DUMMY_BRANCH_ID);
    }

    public function testUpdateProperties()
    {
        $this->branchRepository->expects($this->once())->method('get')->will($this->returnValue($this->branch));

        $name = 'DUMMY_NAME_UPDT';
        $description = 'DUMMY_DESC_UPDT';

        $this->branch->expects($this->exactly(2))->method('updateProperty');

        $this->branchRepository->expects($this->once())->method('store')->with($this->equalTo($this->branch));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo('Entity:DiamanteDeskBundle:Branch'))
            ->will($this->returnValue(true));

        $command = new UpdatePropertiesCommand();
        $command->id = 1;
        $command->properties = [
            ['name' => 'name', 'value' => $name],
            ['name' => 'description', 'value' => $description]
        ];

        $this->branchServiceImpl->updateProperties($command);
    }

    /**
     * @test
     */
    public function testBranchesFiltered()
    {
        $branches = array(new Branch('DUMMY_NAME_1', "DUMMY_DESC"), new Branch("DUMMY_NAME_2","DUMMY_DESC"));

        $this->branchRepository = new DoctrineGenericRepository($this->em, new ClassMetadata('Diamante\DeskBundle\Entity\Branch'));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork
            ->expects($this->atLeastOnce())
            ->method('getEntityPersister')
            ->with($this->equalTo('Diamante\DeskBundle\Entity\Branch'))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister
            ->expects($this->atLeastOnce())
            ->method('loadAll')
            ->will($this->returnValue($branches));

        $this->branchServiceImpl = new BranchServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager,
            $this->securityFacade
        );

        $filtered = $this->branchServiceImpl->filterBranches($this->getCorrectFilteringParams());

        $this->assertEquals(1, count($filtered));

        $filteredBranch = $filtered[0];
        $comparativeBranch = $branches[0];

        $this->assertEquals($comparativeBranch, $filteredBranch);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid filtering constraint 'nonExistentFilteringConstraint' used. Should be one of these: andX, orX, eq, neq, gt, gte, lt, lte, isNull, in, notIn, contains
     */
    public function testExceptionThrownIfUsingIncorrectFilteringConstraint()
    {
        $branches = array(new Branch('DUMMY_NAME_1', "DUMMY_DESC"), new Branch("DUMMY_NAME_2","DUMMY_DESC"));

        $this->branchRepository = new DoctrineGenericRepository($this->em, new ClassMetadata('Diamante\DeskBundle\Entity\Branch'));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork
            ->expects($this->atLeastOnce())
            ->method('getEntityPersister')
            ->with($this->equalTo('Diamante\DeskBundle\Entity\Branch'))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister
            ->expects($this->atLeastOnce())
            ->method('loadAll')
            ->will($this->returnValue($branches));

        $this->branchServiceImpl = new BranchServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager,
            $this->securityFacade
        );

        $this->branchServiceImpl->filterBranches($this->getIncorrectFilteringParams());
    }

    protected function getCorrectFilteringParams()
    {
        return array(
            array(
                'name',
                'eq',
                'DUMMY_NAME_1'
            )
        );
    }

    protected function getIncorrectFilteringParams()
    {
        return array(
            array(
                'name',
                'nonExistentFilteringConstraint',
                'DUMMY_NAME_1'
            )
        );
    }

}
