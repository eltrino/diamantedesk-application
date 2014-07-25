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

    public function testCreateBranch()
    {
        $crawler = $this->client->request(
            'GET', $this->client->generate('diamante_branch_create')
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        $branchName = md5(time());
        $form['diamante_branch_form[name]']        = $branchName;
        $form['diamante_branch_form[description]'] = 'Test Description';
        $form['diamante_branch_form[logoFile]']    = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg';

        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Branch saved", $crawler->html());
        $this->assertTrue($crawler->filter('html:contains("Dproject.png")')->count() == 0);

        return $branchName;
    }

    /**
     * @depends testCreateBranch
     */
    public function testCreateTicketWithBranchName($branchName)
    {
        $branch = $this->chooseBranchFromGridByName($branchName);
        $crawler = $this->client->request(
            'GET', $this->client->generate('diamante_ticket_create',  array('id' => $branch['id']))
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
        $this->assertContains("Ticket created", $crawler->html());

        $uri = $this->client->getRequest()->getUri();
        $uriArray = explode('/', $uri);
        $ticketId = array_pop($uriArray);

        return $ticketId;
    }

    /**
     * @depends testCreateTicketWithBranchName
     */
    public function testCreate($ticketId)
    {
        $crawler = $this->client->request(
            'GET', $this->client->generate('diamante_comment_create', array('id' => $ticketId))
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Add')->form();
        $form['diamante_comment_form[content]'] = 'Test Comment';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment successfully created.", $crawler->html());

        return $ticketId;
    }

    /**
     * @depends testCreate
     */
    public function testUpdate($ticketId)
    {
        $ticketViewUrl = $this->client->generate('diamante_ticket_view', array('id' => $ticketId));
        $crawler = $this->client->request('GET', $ticketViewUrl);
        $link = $crawler->filter('.diam-comments a:contains("Edit")')->eq(0)->link();
        $crawler = $this->client->click($link);

        /** @var Form $form */
        $form = $crawler->selectButton('Edit')->form();
        $form['diamante_comment_form[content]'] = 'Updated comment';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Comment successfully saved.", $crawler->html());

        return $ticketId;
    }

    private function chooseBranchFromGridByName($name)
    {
        $response = ToolsAPI::getEntityGrid(
            $this->client,
            'diamante-branch-grid',
            array(
                'diamante-branch-grid[_filter][name][value]' => $name,
            )
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = ToolsAPI::jsonToArray($response->getContent());
        return current($result['data']);
    }
}
