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
use Diamante\ApiBundle\Routine\Tests\Command\ApiCommand;
use FOS\Rest\Util\Codes;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\User\User;

class TicketApiTest extends ApiTestCase
{
    /**
     * @var ApiCommand
     */
    protected $command;

    public function setUp()
    {
        parent::setUp();
        $this->command = new ApiCommand();
    }

    public function testListTickets()
    {
        $this->getAll('diamante_ticket_api_service_oro_list_all_tickets');
    }

    public function testCreateTicket()
    {
        $this->command->requestParameters = array(
            'branch'      => 1,
            'subject'     => 'Test Ticket',
            'description' => 'Test Description',
            'status'      => 'open',
            'priority'    => Priority::PRIORITY_MEDIUM,
            'source'      => Source::PHONE,
            'reporter'    => User::TYPE_ORO . User::DELIMITER . 1
        );

        $this->post('diamante_ticket_api_service_oro_create_ticket', $this->command);
    }

    public function testGetTicket()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->get('diamante_ticket_api_service_oro_load_ticket', $this->command);
    }

    public function testGetTicketByKey()
    {
        $this->command->urlParameters = array('key' => 'BRANCHE-1');
        $this->get('diamante_ticket_api_service_oro_load_ticket_by_key', $this->command);
    }

    public function testUpdateTicket()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->command->requestParameters = array(
            'subject' => 'Test Ticket Updated PUT'
        );
        $this->put('diamante_ticket_api_service_oro_update_properties', $this->command);

        $this->command->urlParameters = array('id' => 3);
        $this->command->requestParameters['subject'] = 'Test Ticket Updated PATCH';
        $this->patch('diamante_ticket_api_service_oro_update_properties', $this->command);
    }

    public function testDeleteTicket()
    {
        $this->command->urlParameters = array('id' => 1);
        $this->delete('diamante_ticket_api_service_oro_delete_ticket', $this->command);
        $this->get('diamante_ticket_api_service_oro_load_ticket', $this->command, Codes::HTTP_NOT_FOUND);
    }

    public function testDeleteTicketByKey()
    {
        $this->command->urlParameters = array('key' => 'BRANCHE-1');
        $this->delete('diamante_ticket_api_service_oro_delete_ticket_by_key', $this->command);
        $this->get('diamante_ticket_api_service_oro_load_ticket_by_key', $this->command, Codes::HTTP_NOT_FOUND);
    }

    public function testListTicketAttachments()
    {
        $this->command->urlParameters = array('id' => 2);
        $this->get('diamante_ticket_api_service_oro_list_ticket_attachments', $this->command);
    }

    public function testAddAttachmentToTicket()
    {
        $attachment = file_get_contents(
            dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg'
        );
        $this->command->urlParameters = array('ticketId' => 2);
        $this->command->requestParameters = array(
            'attachmentsInput' => array(
                array(
                    'filename' => 'test.jpg',
                    'content'  => base64_encode($attachment)
                )
            )
        );

        return $this->post('diamante_ticket_api_service_oro_add_attachments_for_ticket', $this->command);
    }

    /**
     * @depends testAddAttachmentToTicket
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function testGetTicketAttachment($response)
    {
        $attachmentId = $this->getByKey($response, 'id');
        $this->command->urlParameters = array('ticketId' => 2, 'attachmentId' => $attachmentId);
        $this->get('diamante_ticket_api_service_oro_get_ticket_attachment', $this->command);
    }

    /**
     * @depends testAddAttachmentToTicket
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function testDeleteTicketAttachment($response)
    {
        $attachmentId = $this->getByKey($response, 'id');
        $this->command->urlParameters = array('ticketId' => 2, 'attachmentId' => $attachmentId);
        $this->delete('diamante_ticket_api_service_oro_remove_attachment_from_ticket', $this->command);
        $this->get('diamante_ticket_api_service_oro_get_ticket_attachment', $this->command, Codes::HTTP_NOT_FOUND);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param string                                     $key
     */
    protected function getByKey($response, $key)
    {
        return self::jsonToArray($response->getContent())[0][$key];
    }
}
