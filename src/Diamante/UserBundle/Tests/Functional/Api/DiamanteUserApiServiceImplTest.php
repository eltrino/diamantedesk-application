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
namespace Diamante\UserBundle\Tests\Functional\Api;

use Diamante\ApiBundle\Routine\Tests\ApiTestCase;
use Diamante\ApiBundle\Routine\Tests\Command\ApiCommand;
use FOS\RestBundle\Util\Codes;

class DiamanteUserApiServiceImplTest extends ApiTestCase
{
    /**
     * @var ApiCommand
     */
    protected $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new ApiCommand();
        $this->isDiamante = true;
    }

    /**
     * @return array
     */
    public function testCreateDiamanteUser()
    {
        $this->command->requestParameters = [
            'email'     => time() . 'dummy-test-email-address@test-server.local',
            'firstName' => 'John',
            'lastName'  => 'Dou',
        ];
        $response = $this->post('diamante_user_api_service_create_diamante_user', $this->command);

        return $this->getArray($response);
    }

    /**
     * @depends testCreateDiamanteUser
     *
     * @param array $userData
     * @return array
     */
    public function testGetExistingUser($userData)
    {
        $this->command->urlParameters = ['email'  => $userData['email']];
        $response =  $this->get('diamante_user_api_service_get_user', $this->command);
        return $this->getArray($response);
    }


    /**
     * @return array
     */
    public function testGetNotExistingUser()
    {
        $this->command->urlParameters = ['email'  => 'not-existing-email-address@test-server.local'];
        $response =  $this->get('diamante_user_api_service_get_user', $this->command, Codes::HTTP_NOT_FOUND);
        return $this->getArray($response);
    }

    /**
     * @return array
     */
    public function testGetUsers()
    {
        $response =  $this->get('diamante_user_api_service_get_users', $this->command);
        return $this->getArray($response);
    }

    /**
     * @depends testCreateDiamanteUser
     *
     * @param array $userData
     * @return array
     */
    public function testDeleteExistingUser($userData)
    {
        $this->command->urlParameters = ['id'  => $userData['id']];
        $response =  $this->get('diamante_user_api_service_delete_user', $this->command);
        return $this->getArray($response);
    }

    /**
     * @depends testCreateDiamanteUser
     *
     * @param array $userData
     * @return array
     */
    public function testDeleteNonExistingUser($userData)
    {
        $this->command->urlParameters = ['id'  => 'non-existing-id'];
        $response =  $this->get('diamante_user_api_service_delete_user', $this->command);
        return $this->getArray($response);
    }
}
