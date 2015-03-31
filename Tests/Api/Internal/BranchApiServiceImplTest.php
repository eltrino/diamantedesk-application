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


use Diamante\DeskBundle\Api\Command\Filter\FilterBranchesCommand;
use Diamante\DeskBundle\Api\Internal\BranchApiServiceImpl;
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Model\Shared\Filter\FilterPagingProperties;
use Diamante\DeskBundle\Model\Shared\Filter\PagingInfo;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class BranchApiServiceImplTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $branchRepository;

    /**
     * @var BranchApiServiceImpl
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
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var \Diamante\DeskBundle\Api\ApiPagingService
     * @Mock Diamante\DeskBundle\Api\ApiPagingService
     */
    private $apiPagingService;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->branchServiceImpl = new BranchApiServiceImpl(
            $this->branchFactory,
            $this->branchRepository,
            $this->branchLogoHandler,
            $this->tagManager,
            $this->authorizationService,
            $this->userService
        );

        $this->branchServiceImpl->setApiPagingService($this->apiPagingService);
    }

    /**
     * @test
     */
    public function testBranchesAreFiltered()
    {
        $branches = array(
            new Branch('DUMM', 'DUMMY_NAME_1', 'DUMMY_DESC_1'),
            new Branch('DUMMY', 'DUMMY_NAME_2', 'DUMMY_DESC_2')
        );

        $pagingInfo = new PagingInfo(1, new FilterPagingProperties());

        $command = new FilterBranchesCommand();
        $command->name = 'NAME_2';

        $this->branchRepository
            ->expects($this->once())
            ->method('filter')
            ->with($this->equalTo(array(array('name','like','NAME_2'))), $this->equalTo(new FilterPagingProperties()))
            ->will($this->returnValue(array($branches[0])));

        $this->apiPagingService
            ->expects($this->once())
            ->method('getPagingInfo')
            ->will($this->returnValue($pagingInfo));

        $retrievedBranches = $this->branchServiceImpl->listAllBranches($command);

        $this->assertNotNull($retrievedBranches);
        $this->assertTrue(is_array($retrievedBranches));
        $this->assertNotEmpty($retrievedBranches);
        $this->assertEquals($branches[0], $retrievedBranches[0]);
    }
}
