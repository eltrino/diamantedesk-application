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
namespace Diamante\AutomationBundle\Tests\Functional\Controller;

use Diamante\DeskBundle\Tests\Functional\Controller\AbstractController;

/**
 * Class AttachmentControllerTest
 *
 * @package Diamante\AutomationBundle\Tests\Functional\Controller
 */
class AutomationControllerTest extends AbstractController
{
    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader('admin', '123123q'), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testEventTriggeredList()
    {
        $this->ruleList('event_triggered');
    }

    public function testTimeTriggeredList()
    {
        $this->ruleList('time_triggered');
    }

    private function ruleList($type)
    {
        $crawler  = $this->client->request('GET', $this->getUrl('diamante_automation_list', ['type' => $type]));
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertTrue($crawler->filter('html:contains("Rules")')->count() >= 1);
    }
}
