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
use FOS\Rest\Util\Codes;
use Diamante\ApiBundle\Routine\Tests\Command\ApiCommand;

abstract class ApiTestCase extends WebTestCase
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

    public function getAll($url)
    {
        return $this->request('GET', $url, Codes::HTTP_OK);
    }

    public function get($url, ApiCommand $command, $code = Codes::HTTP_OK)
    {
        return $this->request('GET', $url, $code, $command->urlParameters);
    }

    public function post($url, ApiCommand $command)
    {
        return $this->request('POST', $url, Codes::HTTP_CREATED, $command->urlParameters, $command->requestParameters);
    }

    public function put($url, ApiCommand $command)
    {
        return $this->request('PUT', $url, Codes::HTTP_OK, $command->urlParameters, $command->requestParameters);
    }

    public function patch($url, ApiCommand $command)
    {
        return $this->request('PATCH', $url, Codes::HTTP_OK, $command->urlParameters, $command->requestParameters);
    }

    public function delete($url, ApiCommand $command)
    {
        return $this->request(
            'DELETE',
            $url,
            Codes::HTTP_NO_CONTENT,
            $command->urlParameters,
            array(),
            'assertEmptyResponseStatusCodeEquals'
        );
    }

    protected function request(
        $method,
        $url,
        $code,
        $urlParameters = array(),
        $requestParameters = array(),
        $assert = 'assertJsonResponseStatusCodeEquals'
    ) {
        $this->client->request(
            $method,
            $this->getUrl($url, $urlParameters),
            $requestParameters
        );
        $result = $this->client->getResponse();
        call_user_func(array($this, $assert), $result, $code);

        return $result;
    }
}
