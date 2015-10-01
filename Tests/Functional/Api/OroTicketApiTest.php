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
use Diamante\UserBundle\Model\User;
use FOS\RestBundle\Util\Codes;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Oro\Bundle\TagBundle\Entity\Tag;

class OroTicketApiTest extends ApiTestCase
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
        $response = $this->post('diamante_ticket_api_service_oro_create_ticket', $this->command);

        return $this->getArray($response);
    }

    public function testCreateTicketWithTag()
    {
        $entityManager = static::$kernel->getContainer()->get('doctrine')->getManager();
        $tagRepository = $entityManager->getRepository('OroTagBundle:Tag');
        $tag = $tagRepository->findOneBy(['id' => 1]);
        $uniqueTagName = sprintf('test tag %d', microtime(true) * 1000);
        $tag->setName($uniqueTagName);

        $this->command->requestParameters = array(
            'branch'      => 1,
            'subject'     => 'Test Ticket with tag',
            'description' => 'Test Description with tag',
            'status'      => 'open',
            'priority'    => Priority::PRIORITY_MEDIUM,
            'source'      => Source::PHONE,
            'reporter'    => User::TYPE_ORO . User::DELIMITER . 1,
            'tags' => array(
                'all'          => array($tag),
                'owner'        => array($tag)
            ),
        );
        $response = $this->post('diamante_ticket_api_service_oro_create_ticket', $this->command);

        return $this->getArray($response);
    }

    public function testListTickets()
    {
        $this->getAll('diamante_ticket_api_service_oro_list_all_tickets');
    }

    /**
     * @depends testCreateTicket
     *
     * @param array $ticket
     */
    public function testGetTicket($ticket)
    {
        $this->command->urlParameters = array('id' => $ticket['id']);
        $this->get('diamante_ticket_api_service_oro_load_ticket', $this->command);
    }

    /**
     * @depends testCreateTicket
     *
     * @param array $ticket
     */
    public function testGetTicketByKey($ticket)
    {
        $this->command->urlParameters = array('key' => $ticket['key']);
        $this->get('diamante_ticket_api_service_oro_load_ticket_by_key', $this->command);
    }

    /**
     * @depends testCreateTicket
     *
     * @param array $ticket
     */
    public function testUpdateTicket($ticket)
    {
        $this->command->urlParameters = array('id' => $ticket['id']);
        $this->command->requestParameters = array(
            'subject' => 'Test Ticket Updated PUT'
        );
        $this->put('diamante_ticket_api_service_oro_update_properties', $this->command);

        $this->command->urlParameters = array('id' => $ticket['id']);
        $this->command->requestParameters['subject'] = 'Test Ticket Updated PATCH';
        $this->patch('diamante_ticket_api_service_oro_update_properties', $this->command);
    }

    /**
     * @depends testCreateTicket
     *
     * @param array $ticket
     */
    public function testUpdateTicketByKey($ticket)
    {
        $this->command->urlParameters = array('key' => $ticket['key']);
        $this->command->requestParameters = array(
            'subject' => 'Test Ticket Updated PUT by key'
        );
        $this->put('diamante_ticket_api_service_oro_update_properties_by_key', $this->command);

        $this->command->urlParameters = array('key' => $ticket['key']);
        $this->command->requestParameters['subject'] = 'Test Ticket Updated PATCH by key';
        $this->patch('diamante_ticket_api_service_oro_update_properties_by_key', $this->command);
    }

    /**
     * @depends testCreateTicket
     *
     * @param array $ticket
     *
     * @return array
     */
    public function testDeleteTicket($ticket)
    {
        $this->command->urlParameters = array('id' => $ticket['id']);
        $this->delete('diamante_ticket_api_service_oro_delete_ticket', $this->command);
        $this->get('diamante_ticket_api_service_oro_load_ticket', $this->command, Codes::HTTP_NOT_FOUND);

        return $this->testCreateTicket();
    }

    /**
     * @depends testDeleteTicket
     *
     * @param array $ticket
     *
     * @return array
     */
    public function testDeleteTicketByKey($ticket)
    {
        $this->command->urlParameters = array('key' => $ticket['key']);
        $this->delete('diamante_ticket_api_service_oro_delete_ticket_by_key', $this->command);
        $this->get('diamante_ticket_api_service_oro_load_ticket_by_key', $this->command, Codes::HTTP_NOT_FOUND);

        return $this->testCreateTicket();
    }

    /**
     * @depends testDeleteTicketByKey
     *
     * @param array $ticket
     *
     * @return array
     */
    public function testAddAttachmentToTicket($ticket)
    {
        $file = realpath(
            dirname(__FILE__) . '/../' . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg'
        );
        $attachment = file_get_contents($file);
        $this->command->urlParameters = array('ticketId' => $ticket['id']);
        $this->command->requestParameters = array(
            'attachmentsInput' => array(
                array(
                    'filename' => 'test.jpg',
                    'content'  => base64_encode($attachment)
                )
            )
        );
        $response = $this->post('diamante_ticket_api_service_oro_add_attachments_for_ticket', $this->command);

        return $this->getArray($response);
    }

    /**
     * @depends testDeleteTicketByKey
     *
     * @param array $ticket
     *
     * @return array
     */
    public function testListTicketAttachments($ticket)
    {
        $this->command->urlParameters = array('id' => $ticket['id']);
        $response = $this->get('diamante_ticket_api_service_oro_list_ticket_attachments', $this->command);

        return $this->getArray($response);
    }

    /**
     * @depends testDeleteTicketByKey
     * @depends testAddAttachmentToTicket
     *
     * @param array $ticket
     * @param array $attachments
     */
    public function testGetTicketAttachment($ticket, $attachments)
    {
        $this->command->urlParameters = array('ticketId' => $ticket['id'], 'attachmentId' => $attachments[0]['id']);
        $this->get('diamante_ticket_api_service_oro_get_ticket_attachment', $this->command);
    }

    /**
     * @depends testDeleteTicketByKey
     * @depends testAddAttachmentToTicket
     *
     * @param array $ticket
     * @param array $attachments
     */
    public function testDeleteTicketAttachment($ticket, $attachments)
    {
        $this->command->urlParameters = array('ticketId' => $ticket['id'], 'attachmentId' => $attachments[0]['id']);
        $this->delete('diamante_ticket_api_service_oro_remove_attachment_from_ticket', $this->command);
        $this->get('diamante_ticket_api_service_oro_get_ticket_attachment', $this->command, Codes::HTTP_NOT_FOUND);
    }
}
