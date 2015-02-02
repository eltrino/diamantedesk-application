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
namespace Diamante\EmbeddedFormBundle\Tests\Controller;

use Diamante\DeskBundle\Model\Branch\DefaultBranchKeyGenerator;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

class DiamanteEmbeddedFormControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testView()
    {
        $form        = $this->chooseEmbeddedFormFromGrid();
        $formViewUrl = $this->getUrl('diamante_embedded_form_submit', array('id' => $form['id']));
        $crawler       = $this->client->request('GET', $formViewUrl);
        $response      = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));

        $this->assertTrue($crawler->filter('html:contains("First Name")')->count() >= 1);

        $this->assertTrue($crawler->filter('html:contains("Last Name")')->count() == 1);

        $this->assertTrue($crawler->filter('html:contains("Email")')->count() == 1);
    }

    public function testSubmit()
    {
        $form        = $this->chooseEmbeddedFormFromGrid();
        $formViewUrl = $this->getUrl('diamante_embedded_form_submit', array('id' => $form['id']));
        $crawler       = $this->client->request('GET', $formViewUrl);

        /** @var Form $form */
        $form = $crawler->selectButton('Submit')->form();

        $form['diamante_embedded_form[firstName]']          = 'John';
        $form['diamante_embedded_form[lastName]']           = 'Smith';
        $form['diamante_embedded_form[emailAddress]']       = 'john.smith@gmail.com';
        $form['diamante_embedded_form[subject]']            = 'Subject of ticket';
        $form['diamante_embedded_form[description]']        = 'Write something about problem';

        $this->client->followRedirects(true);

        $crawler  = $this->client->submit($form);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains("Ticket has been placed successfully", $crawler->html());
    }

    private function chooseEmbeddedFormFromGrid()
    {
        $response = $this->client->requestGrid(
            'embedded-forms-grid'
        );

        $this->assertEquals(200, $response->getStatusCode());

        $result = $this->jsonToArray($response->getContent());

        foreach ($result['data'] as $row) {
            if ($row['formType'] == 'Diamante Ticket') {
                return $row;
            }
        }

        return 0;
    }
}