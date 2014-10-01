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
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

class BranchControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected  $client;

    protected $imagesDirectory;

    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->imagesDirectory = realpath($this->client->getKernel()->getRootDir() . '/../web')
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'diamante' . DIRECTORY_SEPARATOR . 'branch'
            . DIRECTORY_SEPARATOR . 'logos';
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', $this->getUrl('diamante_branch_list'));
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Branches")')->count() == 1);
    }

    public function testCreate()
    {
        $crawler = $this->client->request(
            'GET', $this->getUrl('diamante_branch_create')
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        $branchName = md5(time());
        $form['diamante_branch_form[name]']        = $branchName;
        $form['diamante_branch_form[description]'] = 'Test Description';
        $form['diamante_branch_form[logoFile]']    = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg';

        $form['diamante_branch_form[branch_email_configuration][supportAddress]'] = 'test@gmail.com';
        $form['diamante_branch_form[branch_email_configuration][customerDomains]'] = 'gmail.com, yahoo.com';

        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Branch successfully created.", $crawler->html());
        $this->assertTrue($crawler->filter('html:contains("Dproject.png")')->count() == 0);
    }

    public function testView()
    {
        $branch          = $this->chooseBranchFromGrid();
        $branchViewUrl = $this->getUrl('diamante_branch_view', array('id' => $branch['id']));
        $crawler        = $this->client->request('GET', $branchViewUrl);
        $response       = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Branch Details")')->count() >= 1);

        $this->assertTrue($crawler->filter('html:contains("Tickets")')->count() == 1);
    }

    public function testUpdate()
    {
        $branch          = $this->chooseBranchFromGrid();
        $branchUpdateUrl = $this->getUrl('diamante_branch_update', array('id' => $branch['id']));
        $crawler          = $this->client->request('GET', $branchUpdateUrl);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        $form['diamante_branch_form[name]'] = $branch['name'];
        $form['diamante_branch_form[description]'] = 'Branch Description Changed';
        $form['diamante_branch_form[logoFile]'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg';

        $form['diamante_branch_form[branch_email_configuration][supportAddress]'] = 'test@gmail.com';
        $form['diamante_branch_form[branch_email_configuration][customerDomains]'] = 'gmail.com, yahoo.com';

        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Branch successfully saved.", $crawler->html());
        $this->assertTrue($crawler->filter('html:contains("Dproject.png")')->count() == 0);
    }

    public function testDelete()
    {
        $branch          = $this->chooseBranchFromGrid();
        $branchDeleteUrl = $this->getUrl('diamante_branch_delete', array('id' => $branch['id']));
        $crawler          = $this->client->request('GET', $branchDeleteUrl);
        $response         = $this->client->getResponse();

        $viewRequest  = $this->client->request(
            'GET',
            $this->getUrl('diamante_branch_view', array('id' => $branch['id']))
        );
        $viewResponse = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(404, $viewResponse->getStatusCode());

        $this->assertFalse(file_exists($this->imagesDirectory . DIRECTORY_SEPARATOR . $branch['logo']));
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
}
