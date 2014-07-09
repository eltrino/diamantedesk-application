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
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

class BranchControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    private $client;

    private $imagesDirectory;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->imagesDirectory = realpath($this->client->getKernel()->getRootDir() . '/../web')
            . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'diamante' . DIRECTORY_SEPARATOR . 'branch'
            . DIRECTORY_SEPARATOR . 'logos';
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', $this->client->generate('diamante_branch_list'));
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Branches")')->count() == 1);
    }

    public function testCreate()
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
     * @depends testCreate
     */
    public function testView($branchName)
    {
        $branch          = $this->chooseBranchByNameFromGrid($branchName);
        $branchViewUrl = $this->client->generate('diamante_branch_view', array('id' => $branch['id']));
        $crawler        = $this->client->request('GET', $branchViewUrl);
        $response       = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Branch Details")')->count() >= 1);

        $this->assertTrue($crawler->filter('html:contains("Tickets")')->count() == 1);
    }

    /**
     * @depends testCreate
     */
    public function testUpdate($branchName)
    {
        $branch          = $this->chooseBranchByNameFromGrid($branchName);
        $branchUpdateUrl = $this->client->generate('diamante_branch_update', array('id' => $branch['id']));
        $crawler          = $this->client->request('GET', $branchUpdateUrl);

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        //$branchName = md5(time());
        $form['diamante_branch_form[name]'] = $branchName;
        $form['diamante_branch_form[description]'] = 'Branch Description Changed';
        $form['diamante_branch_form[logoFile]'] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'test.jpg';
        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Branch saved", $crawler->html());
        $this->assertTrue($crawler->filter('html:contains("Dproject.png")')->count() == 0);

        return $branchName;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete($branchName)
    {
        $branch          = $this->chooseBranchByNameFromGrid($branchName);
        $branchDeleteUrl = $this->client->generate('diamante_branch_delete', array('id' => $branch['id']));
        $crawler          = $this->client->request('GET', $branchDeleteUrl);
        $response         = $this->client->getResponse();

        $viewRequest  = $this->client->request(
            'GET',
            $this->client->generate('diamante_branch_view', array('id' => $branch['id']))
        );
        $viewResponse = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(404, $viewResponse->getStatusCode());

        $this->assertFalse(file_exists($this->imagesDirectory . DIRECTORY_SEPARATOR . $branch['logo']));
    }

    private function chooseBranchFromGrid()
    {
        $response = ToolsAPI::getEntityGrid(
            $this->client,
            'diamante-branch-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = ToolsAPI::jsonToArray($response->getContent());
        return current($result['data']);
    }

    private function chooseBranchByNameFromGrid($name)
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
