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
namespace Diamante\DeskBundle\Tests\Functional\Controller;

use Diamante\DeskBundle\Model\Ticket\Status;

class TicketWidgetControllerTest extends AbstractController
{
    use Shared\TicketGridTrait;
    
    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testChangeStatus()
    {
        $ticket              = $this->chooseTicketFromGrid();
        $updateStatusFormUrl = $this->getUrl('diamante_ticket_status_change', array('id' => $ticket['id']));
        $crawler             = $this->client->request('GET', $updateStatusFormUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }


    public function testMove()
    {
        $ticket              = $this->chooseTicketFromGrid();
        $moveFormUrl = $this->getUrl('diamante_ticket_move', array('id' => $ticket['id']));
        $crawler             = $this->client->request('GET', $moveFormUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }

    public function testAssign()
    {
        $ticket        = $this->chooseTicketFromGrid();
        $ticketAssignUrl = $this->getUrl('diamante_ticket_assign', array('id' => $ticket['id']));
        $crawler = $this->client->request('GET', $ticketAssignUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }

    /**
     * @group mass
     */
    public function testMassAssign()
    {
        $ticketMassAssignUrl = $this->getUrl('diamante_ticket_mass_assign');
        $crawler = $this->client->request('GET', $ticketMassAssignUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }


    /**
     * @group mass
     */
    public function testMassChangeStatus()
    {
        $ticketMassChangeUrl = $this->getUrl('diamante_ticket_mass_status_change');
        $crawler = $this->client->request('GET', $ticketMassChangeUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }

    /**
     * @group mass
     */
    public function testMassMove()
    {
        $ticketMassMoveUrl = $this->getUrl('diamante_ticket_mass_move');
        $crawler = $this->client->request('GET', $ticketMassMoveUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }

    /**
     * @group mass
     */
    public function testMassWatch()
    {
        $ticketMassWatchUrl = $this->getUrl('diamante_ticket_mass_add_watcher');
        $crawler = $this->client->request('GET', $ticketMassWatchUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Add", $crawler->selectButton('Add')->html());
    }


    /**
     * @group mass
     */
    public function testMassAssignSubmit()
    {
        $ticketIdentifiers = $this->getTicketsId();
        $ticketMassAssignSubmitUrl = $this->getUrl(
            'diamante_ticket_mass_assign',
            [
                'no_redirect' => 'false',
                'ids'         => $ticketIdentifiers,
                'assignee'    => '1'
            ]
        );

        $this->client->request('GET', $ticketMassAssignSubmitUrl);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group mass
     */
    public function testMassChangeStatusSubmit()
    {
        $ticketIdentifiers = $this->getTicketsId();
        $ticketMassChangeSubmitUrl = $this->getUrl(
            'diamante_ticket_mass_status_change',
            ['no_redirect' => 'false', 'ids' => $ticketIdentifiers, 'status' => Status::NEW_ONE]
        );

        $this->client->request('GET', $ticketMassChangeSubmitUrl);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group mass
     */
    public function testMassMoveSubmit()
    {
        $ticketIdentifiers = $this->getTicketsId();
        $ticketMassMoveSubmitUrl = $this->getUrl(
            'diamante_ticket_mass_move',
            [
                'no_redirect' => 'false',
                'ids'         => $ticketIdentifiers,
                'branch'      => '1'
            ]
        );

        $this->client->request('GET', $ticketMassMoveSubmitUrl);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @group mass
     */
    public function testMassWatchSubmit()
    {
        $ticketIdentifiers = $this->getTicketsId();
        $ticketMassWatchSubmitUrl = $this->getUrl(
            'diamante_ticket_mass_add_watcher',
            [
                'no_redirect' => 'false',
                'ids'         => $ticketIdentifiers,
                'watcher'     => '1'
            ]
        );

        $this->client->request('GET', $ticketMassWatchSubmitUrl);
        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function getTicketsId($quantity = 2)
    {
        $identifiers = [];
        $result = $this->getTicketGridData();
        foreach ($result['data'] as $ticket) {
            $identifiers[] = $ticket['id'];

            if (count($identifiers) == $quantity) {
                break;
            }
        }

        return trim(implode(', ', $identifiers), ', ');
    }
}
