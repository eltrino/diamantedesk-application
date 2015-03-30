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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 4/12/14
 * Time: 4:13 PM
 */

namespace Diamante\DeskBundle\Tests\Search;

use Diamante\DeskBundle\Entity\DiamanteUser;
use Diamante\DeskBundle\Model\User\UserDetails;
use Diamante\DeskBundle\Search\DiamanteUserSearchHandler;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Model\User\User;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class DiamanteUserSearchHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\UserBundle\Model\UserDetailsService
     * @Mock Diamante\DeskBundle\Model\User\UserDetailsService
     */
    private $userDetailsService;

    /**
     * @var \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     * @Mock \Diamante\DeskBundle\Model\User\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var \Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler
     * @Mock Oro\Bundle\UserBundle\Autocomplete\UserSearchHandler
     */
    private $userSearchHandler;

    /**
     * @var DiamanteUserSearchHandler
     */
    private $diamanteUserSearchHandler;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->diamanteUserSearchHandler = new DiamanteUserSearchHandler(
            'diamante_user',
            $this->userDetailsService,
            $this->diamanteUserRepository,
            $this->userSearchHandler,
            $this->getProperties()
        );
    }

    /**
     * @test
     */
    public function testConvertItem()
    {
        $id = 'diamante_1';
        $userObj = User::fromString($id);
        $userDetails = $this->createUserDetails(User::TYPE_DIAMANTE);

        $this->userDetailsService
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($userObj))
            ->will($this->returnValue($userDetails));

        $result = $this->diamanteUserSearchHandler->convertItem($userObj);

        $this->assertInternalType('array', $result);
        $this->assertEquals($id, $result['id']);
        $this->assertEquals(User::TYPE_DIAMANTE, $result['type']);
    }

    /**
     * @test
     */
    public function testSearchWithEmptyQuery()
    {
        $query = '';

        $this->diamanteUserRepository
            ->expects($this->once())
            ->method('searchByInput')
            ->with($this->equalTo($query), $this->equalTo($this->getProperties()))
            ->will($this->returnValue($this->getDiamanteUsersCollection()));

        $this->userSearchHandler
            ->expects($this->once())
            ->method('search')
            ->with($this->equalTo($query), 1, 10)
            ->will($this->returnValue(array('results' => $this->getOroUsersCollection(), 'more' => false)));

        $result = $this->diamanteUserSearchHandler->search($query, 1, 10);

        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('results', $result));
        $this->assertEquals(4, count($result['results']));
    }

    /**
     * @test
     */
    public function testSearchWithNotEmptyQuery()
    {
        $query = 'Name';

        $expectedDiamanteUsers = 1;
        $expectedOroUsers      = 3;
        $totalExpectedResult   = $expectedDiamanteUsers + $expectedOroUsers;

        $this->diamanteUserRepository
            ->expects($this->once())
            ->method('searchByInput')
            ->with($this->equalTo($query), $this->equalTo($this->getProperties()))
            ->will($this->returnValue($this->getDiamanteUsersCollection($expectedDiamanteUsers, $query)));

        $this->userSearchHandler
            ->expects($this->once())
            ->method('search')
            ->with($this->equalTo($query), 1, 10)
            ->will($this->returnValue(array('results' => $this->getOroUsersCollection($expectedOroUsers, $query), 'more' => false)));

        $result = $this->diamanteUserSearchHandler->search($query, 1, 10);

        $this->assertInternalType('array', $result);
        $this->assertTrue(array_key_exists('results', $result));
        $this->assertEquals($totalExpectedResult, count($result['results']));

        foreach ($result['results'] as $item) {
            $this->assertStringEndsWith($query, $item['firstName']);
        }
    }

    /**
     * @return array
     */
    protected function getProperties()
    {
        return [
            'email',
            'firstName',
            'lastName',
            'fullName',
            'type'
        ];
    }

    /**
     * @param int $size
     * @param string $specificData
     * @return array
     */
    protected function getDiamanteUsersCollection($size = 2, $specificData = '')
    {
        $result = array();

        for ($i = 0; $i < $size; $i++) {
            $user = new DiamanteUser("email@host{$i}.com", null, "First {$specificData}", "Last {$specificData}");
            $result[] = $user;
        }

        return $result;
    }

    /**
     * @param int $size
     * @param string $specificData
     * @return array
     */
    protected function getOroUsersCollection($size = 2, $specificData = '')
    {
        $result = array();

        for ($i = 0; $i < $size; $i++) {
            $user = new OroUser();
            $user->setUsername("username_{$i}");
            $user->setFirstName("First {$specificData}");
            $user->setLastName("Last {$specificData}");
            $user->setEmail("some@host{$i}.com");
            $result[] = $user;
        }

        return $result;
    }

    /**
     * @param $type
     * @return UserDetails
     */
    protected function createUserDetails($type)
    {
        return new UserDetails($type . User::DELIMITER . 1, $type, 'email@example.com', 'First', 'Last');
    }
}
