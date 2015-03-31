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
namespace Diamante\DeskBundle\Tests\Infrastructure\Persistence;

use Diamante\DeskBundle\Model\Branch\EmailProcessing\BranchEmailConfiguration;
use Diamante\DeskBundle\Model\Branch\Branch;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineBranchEmailConfigurationRepository;

class DoctrineBranchEmailConfigurationRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineBranchEmailConfigurationRepository
     */
    private $repository;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     * @Mock \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $classMetadata;

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


    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->classMetadata->name = self::DUMMY_CLASS_NAME;
        $this->repository = new DoctrineBranchEmailConfigurationRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatBranchEmailConfigurationRetrievesByBranchId()
    {
        $branchId = 1;
        $branchEmailConfiguration = $this->getBranchEmailConfiguartion();

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork->expects($this->once())
            ->method('getEntityPersister')
            ->with($this->equalTo(self::DUMMY_CLASS_NAME))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister->expects($this->once())
            ->method('load')
            ->with(
                $this->equalTo(array('branchId' => $branchId)), $this->equalTo(null), $this->equalTo(null), array(), $this->equalTo(0),
                $this->equalTo(1), $this->equalTo(null)
            )->will($this->returnValue($branchEmailConfiguration));

        $retrievedBranchEmailConfiguration = $this->repository->findOneBy(array('branchId' => $branchId));

        $this->assertNotNull($retrievedBranchEmailConfiguration);
        $this->assertEquals($branchEmailConfiguration, $retrievedBranchEmailConfiguration);
    }

    private function getBranchEmailConfiguartion()
    {
        $branchEmailConfiguration = new BranchEmailConfiguration(
            new Branch('DUMM', 'DUMMY_NAME', 'DUMMY_DESCR'),
            'customerDomains',
            'supportAddress'
        );

        return $branchEmailConfiguration;
    }
}
