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
namespace Diamante\DeskBundle\Tests\Functional\Api;

use Diamante\ApiBundle\Routine\Tests\ApiTestCase;
use Diamante\ApiBundle\Routine\Tests\Command\ApiCommand;
use FOS\RestBundle\Util\Codes;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\UserBundle\Model\User;

class DiamanteWatchersApiTest extends ApiTestCase
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
    public function testAddWatcher()
    {
        $diamanteUserEmail = static::$kernel->getContainer()
            ->get('diamante.user.repository')
            ->findUserByEmail($this->diamanteEmail)
            ->getEmail();
        $this->command->urlParameters = array('id' => 1);
        $this->command->requestParameters = array(
            'email' => $diamanteUserEmail,
        );
        $response = $this->post('diamante_watcher_service_api_add_watcher_by_email', $this->command);

        return $this->getArray($response);
    }

    /**
     * @depends testAddWatcher
     *
     * @return array
     */
    public function testRemoveWatcher()
    {
        $diamanteUserId = static::$kernel->getContainer()
            ->get('diamante.user.repository')
            ->findUserByEmail($this->diamanteEmail)
            ->getId();

        $this->command->urlParameters = ['id' => 1, 'userId' => 'diamante_' . $diamanteUserId];
        $this->delete('diamante_watcher_service_api_remove_watcher_by_id', $this->command);
    }

    public function testListWatchers()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->get('diamante_watcher_service_api_list_watchers', $this->command);
    }
}