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

namespace Eltrino\DiamanteDeskBundle\Tests\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;

class CommentControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testCreate()
    {
        $ticket    = $this->chooseTicket();
        $crawler = $this->client->request(
            'GET', $this->client->generate('diamante_comment_create', array('id' => $ticket['id']))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Add')->form();
        $form['diamante_comment_form[content]'] = 'Test Comment';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment saved", $crawler->html());
    }

    public function testUpdate()
    {
        $ticket = $this->chooseTicket();
        $ticketViewUrl = $this->client->generate('diamante_ticket_view', array('id' => $ticket['id']));
        $crawler = $this->client->request('GET', $ticketViewUrl);
        $link = $crawler->filter('.diam-comments a:contains("Edit")')->eq(1)->link();
        $crawler = $this->client->click($link);

        /** @var Form $form */
        $form = $crawler->selectButton('Edit')->form();
        $form['diamante_comment_form[content]'] = 'Updated comment';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment saved", $crawler->html());
    }

    public function testDelete()
    {
        $ticket = $this->chooseTicket();
        $ticketViewUrl = $this->client->generate('diamante_ticket_view', array('id' => $ticket['id']));
        $crawler = $this->client->request('GET', $ticketViewUrl);
        $deleteLink = $crawler->filter('.diam-comments a:contains("Delete")')->eq(1)->link();
        $this->client->click($deleteLink);
        $response = $this->client->getResponse();

        $this->client->click($deleteLink);
        $newResponse = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(404, $newResponse->getStatusCode());
    }

    private function chooseTicket()
    {
        $response = ToolsAPI::getEntityGrid(
            $this->client,
            'diamante-ticket-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = ToolsAPI::jsonToArray($response->getContent());
        return current($result['data']);
    }
}
