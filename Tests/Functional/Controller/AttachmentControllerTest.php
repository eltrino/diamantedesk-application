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
namespace Diamante\DiamanteDeskBundle\Tests\Functional\Controller;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class AttachmentController extends WebTestCase
{
    /**
     * @var \Oro\Bundle\TestFrameworkBundle\Test\Client
     */
    protected $client;

    public function setUp()
    {
        $this->initClient();
    }

    public function testExistingImage()
    {
        $hash = '0b6a8495b50356156408aa35034da503';
        $url = $this->getUrl('diamante_attachment_image_download', ['hash' => $hash]);
        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNonExistingImage()
    {
        $hash = '975cc79b61456be582e289c4e40fdd33';
        $url = $this->getUrl('diamante_attachment_image_download', ['hash' => $hash]);

        $crawler = $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains("Attachment not found", $crawler->html());
    }

    public function testExistingThumbnail()
    {
        $hash = '0b6a8495b50356156408aa35034da503';
        $url = $this->getUrl('diamante_attachment_thumbnail_download', ['hash' => $hash]);
        $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testNonExistingThumbnail()
    {
        $hash = '975cc79b61456be582e289c4e40fdd33';
        $url = $this->getUrl('diamante_attachment_thumbnail_download', ['hash' => $hash]);

        $crawler = $this->client->request('GET', $url);

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertContains("Attachment not found", $crawler->html());

    }
}
