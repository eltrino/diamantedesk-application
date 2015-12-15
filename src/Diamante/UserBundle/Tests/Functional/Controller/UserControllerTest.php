<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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
namespace Diamante\UserBundle\Tests\Functional\Controller;

use Diamante\DeskBundle\Tests\Functional\Controller\AbstractController;

class UserControllerTest extends AbstractController
{
    protected $imagesDirectory;

    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testList()
    {
        $crawler = $this->client->request('GET', $this->getUrl('diamante_user_list'));
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Customers")')->count() == 1);
    }

    public function testView()
    {
        $user = $this->selectDiamanteUser();
        $viewUrl = $this->getUrl('diamante_user_view', ['id' => $user['id']]);
        $crawler = $this->client->request('GET', $viewUrl);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Customer")')->count() >= 1);

        $this->assertTrue($crawler->filter('html:contains("Email")')->count() == 1);

        $this->assertTrue($crawler->filter('html:contains("Is Active")')->count() == 1);
    }

    public function testCreate()
    {
        $createUrl = $this->getUrl('diamante_user_create');
        $crawler = $this->client->request('POST', $createUrl);

        $form = $crawler->selectButton('Save and Close')->form();
        $form['diamante_user_create[email]']        = $this->getRandomMail();
        $form['diamante_user_create[firstName]']    = 'First';
        $form['diamante_user_create[lastName]']     = 'Last';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Customer successfully created.", $crawler->html());
    }

    public function testUpdate()
    {
        $user = $this->selectDiamanteUser();
        $updateUrl = $this->getUrl('diamante_user_update', ['id' => $user['id']]);
        $crawler = $this->client->request('GET', $updateUrl);

        $form = $crawler->selectButton('Save and Close')->form();
        $form['diamante_user_update[email]']        = $this->getRandomMail();
        $form['diamante_user_update[firstName]']    = 'First';
        $form['diamante_user_update[lastName]']     = 'Last';
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Customer successfully updated.", $crawler->html());
    }

    public function testDelete()
    {
        $user = $this->selectDiamanteUser();
        $updateUrl = $this->getUrl('diamante_user_delete', ['id' => $user['id']]);
        $crawler = $this->client->request('POST', $updateUrl);
        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testResetPassword()
    {
        $user = $this->selectDiamanteUser();
        $updateUrl = $this->getUrl('diamante_user_force_reset', ['id' => $user['id']]);
        $crawler = $this->client->request('POST', $updateUrl);
        $response = $this->client->getResponse();

        $this->assertEquals(204, $response->getStatusCode());
    }

    protected function getRandomMail()
    {
        return sprintf('newly_created_user%d@example.com', microtime(true) * 1000);
    }

    /**
     * @return int
     */
    protected function selectDiamanteUser()
    {
        $response = $this->requestGrid('diamante-user-grid');
        $this->assertEquals(200, $response->getStatusCode());

        $result = $this->jsonToArray($response->getContent());
        return current($result['data']);
    }
}
