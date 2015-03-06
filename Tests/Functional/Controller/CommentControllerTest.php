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
 * @todo finish up this test
 */

namespace Diamante\DiamanteDeskBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Diamante\DeskBundle\Model\Ticket\Status;
use Symfony\Component\DomCrawler\Form;

class CommentControllerTest extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('akolomiec', 'akolomiec'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testCreate()
    {
        $ticket = $this->chooseTicket();
        $crawler = $this->client->request(
            'GET', $this->getUrl('diamante_comment_create', array('id' => $ticket['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Add')->form();
        $form['diamante_comment_form[content]'] = 'Test Comment';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment successfully created.", $crawler->html());
    }

    public function testCreateCommentAndChangeTicketStatus()
    {
        $ticket = $this->chooseTicket();
        $crawler = $this->client->request(
            'GET', $this->getUrl('diamante_comment_create', array('id' => $ticket['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Add')->form();
        $form['diamante_comment_form[content]'] = 'Creating comment and setting ticket status to "in progress"';
        $form['diamante_comment_form[ticketStatus]'] = Status::IN_PROGRESS;
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment successfully created.", $crawler->html());
    }

    public function testUpdate()
    {
        $ticket = $this->chooseTicket();
        $ticketViewUrl = $this->getUrl('diamante_ticket_view', array('key' => $ticket['key']));
        $crawler = $this->client->request('GET', $ticketViewUrl);
        $link = $crawler->filter('.diam-comments a:contains("Edit")')->eq(0)->link();
        $crawler = $this->client->click($link);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $form['diamante_comment_form[content]'] = 'Updated comment';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment successfully saved.", $crawler->html());
    }

    public function testUpdateCommentAndChangeTicketStatus()
    {
        $ticket = $this->chooseTicket();
        $ticketViewUrl = $this->getUrl('diamante_ticket_view', array('key' => $ticket['key']));
        $crawler = $this->client->request('GET', $ticketViewUrl);
        $link = $crawler->filter('.diam-comments a:contains("Edit")')->eq(0)->link();
        $crawler = $this->client->click($link);

        /** @var Form $form */
        $form = $crawler->selectButton('Save')->form();
        $form['diamante_comment_form[content]'] = 'Changed ticket status wile updating comment';
        $form['diamante_comment_form[ticketStatus]'] = Status::ON_HOLD;
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment successfully saved.", $crawler->html());
    }

    private function chooseTicket()
    {
        $response = $this->client->requestGrid(
            'diamante-ticket-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = $this->jsonToArray($response->getContent());
        return current($result['data']);
    }
}
