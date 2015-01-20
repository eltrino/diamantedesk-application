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
namespace Diamante\ApiBundle\Tests\Infrastructure\Persistence;

use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\ApiBundle\Infrastructure\Persistence\DoctrineApiUserRepository;
use Diamante\ApiBundle\Model\ApiUser\ApiUser;

class DoctrineApiUserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_CLASS_NAME = 'DUMMY_CLASS_NAME';

    /**
     * @var DoctrineApiUserRepository
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
        $this->repository = new DoctrineApiUserRepository($this->em, $this->classMetadata);
    }

    /**
     * @test
     */
    public function thatApiUserRetrievesByUsername()
    {
        $username = 'test_username';
        $apiUser = $this->getApiUser();

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
                $this->equalTo(array('username' => $username)), $this->equalTo(null), $this->equalTo(null), array(), $this->equalTo(0),
                $this->equalTo(1), $this->equalTo(null)
            )->will($this->returnValue($apiUser));

        $retrievedApiUser = $this->repository->findOneBy(array('username' => $username));

        $this->assertNotNull($retrievedApiUser);
        $this->assertEquals($apiUser, $retrievedApiUser);
    }

    private function getApiUser()
    {
        $apiUser = new ApiUser(
            'test_username',
            'test_user_password',
            'test_user_salt',
            array()
        );

        return $apiUser;
    }
}
