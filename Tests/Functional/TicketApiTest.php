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
namespace Diamante\DeskBundle\Tests\Functional;

use Diamante\ApiBundle\Routine\Tests\ApiTestCase;
use Diamante\ApiBundle\Routine\Tests\ResponseAnalyzer;
use Diamante\ApiBundle\Routine\Tests\ApiCommand;
use FOS\Rest\Util\Codes;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\User\User;

class TicketApiTest extends ApiTestCase
{
    /**
     * @var ResponseAnalyzer
     */
    protected $responseAnalyzer;

    /**
     * @var ApiCommand
     */
    protected $command;

    public function setUp()
    {
        parent::setUp();
        $this->responseAnalyzer = new ResponseAnalyzer();
        $this->command = new ApiCommand();
    }

    public function testList()
    {
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_ticket_api_service_oro_list_all_tickets');
    }

    public function testGetTicket()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('GET', 'diamante_ticket_api_service_oro_load_ticket');
    }

    public function testCreateTicket()
    {
        $this->command->requestParameters = array(
            'branch' => 1,
            'subject' => 'Test Ticket',
            'description' => 'Test Description',
            'status' => 'open',
            'priority' => Priority::PRIORITY_MEDIUM,
            'source' => Source::PHONE,
            'reporter' => User::TYPE_ORO . User::DELIMITER .  1
        );
        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_CREATED);
        $this->request('POST', 'diamante_ticket_api_service_oro_create_ticket');
    }

    public function testUpdateTicket()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->command->requestParameters = array(
            'subject' => 'Test Ticket Updated PUT'
        );

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('PUT', 'diamante_ticket_api_service_oro_update_properties');

        $this->command->urlParameters = array('id' => 3);
        $this->command->requestParameters['subject'] = 'Test Ticket Updated PATCH';

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_OK);
        $this->request('PATCH', 'diamante_ticket_api_service_oro_update_properties');
    }

    public function testDeleteTicket()
    {
        $this->command->urlParameters = array('id' => 1);

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NO_CONTENT);
        $this->request('DELETE', 'diamante_ticket_api_service_oro_delete_ticket');

        $this->responseAnalyzer->expects('getStatusCode')->will(Codes::HTTP_NOT_FOUND);
        $this->request('GET', 'diamante_ticket_api_service_oro_load_ticket');
    }

    public function request($method, $uri)
    {
        parent::request($method, $uri, $this->responseAnalyzer, $this->command);
    }
}
