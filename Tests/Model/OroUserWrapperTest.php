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
namespace Eltrino\DiamanteDeskBundle\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
use \Oro\Bundle\UserBundle\Entity\User as OroUser;
use \Eltrino\DiamanteDeskBundle\Model\OroUserWrapper;

class OroUserWrapperTest extends \PHPUnit_Framework_TestCase
{
    const ORO_USER_ID        = 1;
    const ORO_USER_USERNAME  = 'username';
    const ORO_USER_FIRSTNAME = 'Firstname';
    const ORO_USER_LASTNAME  = 'Lastname';

    /**
     * @var OroUserWrapper
     */
    private $userWrapper;

    private $userManager;

    protected function setUp()
    {
        $this->userManager = $this->getMockBuilder('\ORO\Bundle\UserBundle\Entity\UserManager')
            ->disableOriginalConstructor()
            ->setMethods(array('findUserBy', 'findUsers'))
            ->getMock();
        $this->userWrapper = new OroUserWrapper($this->userManager);
    }

    public function testFindById()
    {
        $this->userManager->expects($this->once())
            ->method('findUserBy')
            ->with($this->equalTo(array('id' => self::ORO_USER_ID)))
            ->will($this->returnValue($this->createOroUser()));

        $user = $this->userWrapper->findById(self::ORO_USER_ID);

        $this->assertEquals(self::ORO_USER_ID, $user->getId());
        $this->assertEquals(self::ORO_USER_USERNAME, $user->getUsername());
        $this->assertEquals(self::ORO_USER_FIRSTNAME . ' ' . self::ORO_USER_LASTNAME, $user->getFullname());
    }

    /**
     * @expectedException \LogicException
     */
    public function testFindByIdThrowsException()
    {
        $emptyOroUser = new OroUser();
        $this->userManager->expects($this->once())
            ->method('findUserBy')
            ->with($this->equalTo(array('id' => self::ORO_USER_ID)))
            ->will($this->returnValue($emptyOroUser));

        $user = $this->userWrapper->findById(1);
    }

    public function testFindAll()
    {
        $oroUsers = new ArrayCollection();
        $oroUsers->add($this->createOroUser());
        $this->userManager->expects($this->once())
            ->method('findUsers')
            ->will($this->returnValue($oroUsers));

        $users = $this->userWrapper->findAll();

        $this->assertCount(1, $users);
        $this->assertEquals(self::ORO_USER_USERNAME, $users->get(0)->getUsername());
    }

    private function createOroUser()
    {
        $oroUser = new OroUser();
        $oroUser->setId(self::ORO_USER_ID);
        $oroUser->setUsername(self::ORO_USER_USERNAME);
        $oroUser->setFirstName(self::ORO_USER_FIRSTNAME);
        $oroUser->setLastName(self::ORO_USER_LASTNAME);
        return $oroUser;
    }
}
