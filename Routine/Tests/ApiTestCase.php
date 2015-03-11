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
namespace Diamante\ApiBundle\Routine\Tests;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Rhumsaa\Uuid\Console\Exception;

class ApiTestCase extends WebTestCase
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

    /**
     * @param string $method
     * @param string $uri
     * @param ResponseAnalyzer $responseAnalyzer
     * @param ApiCommand $command
     */
    public function request($method, $uri, ResponseAnalyzer $responseAnalyzer, ApiCommand $command)
    {
        $this->client->request($method, $this->getUrl($uri, $command->urlParameters), $command->requestParameters);
        try{
            $responseAnalyzer->setResponse($this->client->getResponse())
                ->analyze();
        } catch(Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
