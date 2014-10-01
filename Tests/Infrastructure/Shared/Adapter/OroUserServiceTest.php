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
namespace Diamante\DeskBundle\Tests\Infrastructure\Shared\Adapter;

use Diamante\DeskBundle\Infrastructure\Shared\Adapter\OroUserService;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class OroUserServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OroUserService
     */
    private $service;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\UserManager
     * @Mock \Oro\Bundle\UserBundle\Entity\UserManager
     */
    private $userManager;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new OroUserService($this->userManager);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage User doesn't exist.
     */
    public function thatThrowsExceptionIfUserDoesNotExist()
    {
        $id = 1;
        $this->userManager->expects($this->once())->method('findUserBy')->with($this->equalTo(array('id' => $id)))
            ->will($this->returnValue(null));

        $this->service->getUserById($id);
    }

    /**
     * @test
     */
    public function thatRetrievesUserById()
    {
        $id = 1;
        $entity = new User();
        $this->userManager->expects($this->once())->method('findUserBy')->with($this->equalTo(array('id' => $id)))
            ->will($this->returnValue($entity));

        $user = $this->service->getUserById($id);

        $this->assertEquals($entity, $user);
    }
}
