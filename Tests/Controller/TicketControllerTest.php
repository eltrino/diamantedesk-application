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
namespace Eltrino\DiamanteDeskBundle\Tests\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\UserBundle\Entity\User;

class TicketControllerTest extends WebTestCase
{
    /**
     * @var \Oro\Bundle\TestFrameworkBundle\Test\Client
     */
    protected $client;

    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testCreateWithoutBranchId()
    {
        $crawler = $this->client->request(
            'POST', $this->getUrl('diamante_ticket_create')
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['diamante_ticket_form[branch]']      = $this->chooseBranchFromGrid()['id'];
        $form['diamante_ticket_form[subject]']     = 'Test Ticket';
        $form['diamante_ticket_form[description]'] = 'Test Description';
        $form['diamante_ticket_form[status]']      = 'open';
        $form['diamante_ticket_form[reporter]']    = 1;
        $form['diamante_ticket_form[assignee]']    = 1;
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Ticket successfully created.", $crawler->html());
    }

    public function testCreateWithBranchId()
    {
        $branch = $this->chooseBranchFromGrid();
        $crawler = $this->client->request(
            'GET', $this->getUrl('diamante_ticket_create',  array('id' => $branch['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['diamante_ticket_form[branch]']      = $branch['id'];
        $form['diamante_ticket_form[subject]']     = 'Test Ticket';
        $form['diamante_ticket_form[description]'] = 'Test Description';
        $form['diamante_ticket_form[status]']      = 'open';
        $form['diamante_ticket_form[reporter]']    = 1;
        $form['diamante_ticket_form[assignee]']    = 1;
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Ticket successfully created.", $crawler->html());
    }

    public function testList()
    {
        $crawler  = $this->client->request('GET', $this->getUrl('diamante_ticket_list'));
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Tickets")')->count() >= 1);

        $filtersList = array(
            'All tickets',
            'My tickets',
            'My new tickets',
            'My open tickets',
            'Reported tickets',
            'New reported tickets',
        );

        // test filters
        foreach($filtersList as $filter) {
            $this->assertTrue($crawler->filter('html:contains(' . $filter . ')')->count() >= 1);
            $links = $crawler->selectLink($filter)->links();
            $link = $links[0];
            $this->client->click($link);
            $response = $this->client->getResponse();
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    public function testView()
    {
        $ticket        = $this->chooseTicketFromGrid();
        $ticketViewUrl = $this->getUrl('diamante_ticket_view', array('id' => $ticket['id']));
        $crawler     = $this->client->request('GET', $ticketViewUrl);
        $response    = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Ticket Details")')->count() == 1);

        $this->assertTrue($crawler->filter('html:contains("Attachments")')->count() == 1);
        $this->assertTrue($crawler->filter('html:contains("Comments")')->count() == 1);
    }

    public function testChangeStatus()
    {
        $ticket              = $this->chooseTicketFromGrid();
        $updateStatusFormUrl = $this->getUrl('diamante_ticket_status_change', array('id' => $ticket['id']));
        $crawler             = $this->client->request('GET', $updateStatusFormUrl);

        $this->assertEquals("Cancel", $crawler->selectButton('Cancel')->html());
        $this->assertEquals("Change", $crawler->selectButton('Change')->html());
    }

    public function testUpdate()
    {
        $ticket        = $this->chooseTicketFromGrid();
        $ticketUpdateUrl = $this->getUrl('diamante_ticket_update', array('id' => $ticket['id']));
        $crawler       = $this->client->request('GET', $ticketUpdateUrl);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['diamante_ticket_form[subject]'] = 'Just Changed';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Ticket successfully saved.", $crawler->html());
    }

    public function testAssign()
    {
        $ticket        = $this->chooseTicketFromGrid();
        $ticketAssignUrl = $this->getUrl('diamante_ticket_assign', array('id' => $ticket['id']));
        $crawler = $this->client->request('GET', $ticketAssignUrl);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $form['diamante_ticket_form_assignee[assignee]'] = 315;
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Ticket successfully re-assigned.", $crawler->html());
    }

    public function testDelete()
    {
        $ticket        = $this->chooseTicketFromGrid();
        $ticketDeleteUrl = $this->getUrl('diamante_ticket_delete', array('id' => $ticket['id']));
        $crawler       = $this->client->request('GET', $ticketDeleteUrl);
        $response      = $this->client->getResponse();

        $this->client->request(
            'GET',
            $this->getUrl('diamante_ticket_view', array('id' => $ticket['id']))
        );
        $viewResponse = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(404, $viewResponse->getStatusCode());
    }

    private function chooseBranchFromGrid()
    {
        $response = $this->client->requestGrid(
            'diamante-branch-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = $this->jsonToArray($response->getContent());
        return current($result['data']);
    }

    private function chooseTicketFromGrid()
    {
        $response = $this->client->requestGrid(
            'diamante-ticket-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = $this->jsonToArray($response->getContent());

        return current($result['data']);
    }
}
