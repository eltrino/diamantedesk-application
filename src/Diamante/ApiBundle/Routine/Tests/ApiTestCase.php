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
use FOS\RestBundle\Util\Codes;
use Diamante\ApiBundle\Routine\Tests\Command\ApiCommand;

abstract class ApiTestCase extends WebTestCase
{
    const DIAMANTE_EMAIL = 'admin@eltrino.com';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var bool
     */
    protected $isDiamante;

    /**
     * @var string
     */
    protected $oroUsername;

    /**
     * @var string
     */
    protected $oroApiKey;

    /**
     * @var string
     */
    protected $diamanteEmail;

    public function setUp()
    {
        $this->oroUsername = 'admin';
        $this->oroApiKey = 'api_key';
        $this->diamanteEmail = static::DIAMANTE_EMAIL;
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->initClient();
    }

    /**
     * @param string $url
     * @param int    $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAll($url, $code = Codes::HTTP_OK)
    {
        return $this->request('GET', $url, $code);
    }

    /**
     * @param string     $url
     * @param ApiCommand $command
     * @param int        $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($url, ApiCommand $command, $code = Codes::HTTP_OK)
    {
        return $this->request('GET', $url, $code, $command->urlParameters);
    }

    /**
     * @param string     $url
     * @param ApiCommand $command
     * @param int        $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function post($url, ApiCommand $command, $code = Codes::HTTP_CREATED)
    {
        return $this->request('POST', $url, $code, $command->urlParameters, $command->requestParameters);
    }

    /**
     * @param string     $url
     * @param ApiCommand $command
     * @param int        $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function put($url, ApiCommand $command, $code = Codes::HTTP_OK)
    {
        return $this->request('PUT', $url, $code, $command->urlParameters, $command->requestParameters);
    }

    /**
     * @param string     $url
     * @param ApiCommand $command
     * @param int        $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patch($url, ApiCommand $command, $code = Codes::HTTP_OK)
    {
        return $this->request('PATCH', $url, $code, $command->urlParameters, $command->requestParameters);
    }

    /**
     * @param string     $url
     * @param ApiCommand $command
     * @param int        $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete($url, ApiCommand $command, $code = Codes::HTTP_NO_CONTENT)
    {
        return $this->request(
            'DELETE',
            $url,
            $code,
            $command->urlParameters,
            array(),
            'assertEmptyResponseStatusCodeEquals'
        );
    }

    /**
     * @param string $method
     * @param string $url
     * @param int    $code
     * @param array  $urlParameters
     * @param array  $requestParameters
     * @param string $assert
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function request(
        $method,
        $url,
        $code,
        $urlParameters = array(),
        $requestParameters = array(),
        $assert = 'assertJsonResponseStatusCodeEquals'
    ) {
        $server = $this->generateWsse();
        $this->client->setServerParameters($server);
        $this->client->insulate();
        $this->client->request(
            $method,
            $this->getUrl($url, $urlParameters),
            $requestParameters
        );
        $result = $this->client->getResponse();
        $this->$assert($result, $code);

        return $result;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return array
     */
    public function getArray($response)
    {
        return self::jsonToArray($response->getContent());
    }

    /**
     * @return array
     */
    private function generateWsse()
    {
        if ($this->isDiamante) {
            return $this->generateDiamanteWsseAuthHeader($this->diamanteEmail);
        } else {
            return $this->generateWsseAuthHeader($this->oroUsername, $this->oroApiKey);
        }
    }

    /**
     * @param string $email
     *
     * @return array
     */
    private function generateDiamanteWsseAuthHeader($email)
    {
        $created = date('c');
        $prefix = gethostname();
        $nonce = base64_encode(substr(md5(uniqid($prefix . '_', true)), 0, 16));
        $user = static::$kernel->getContainer()
            ->get('diamante.api.user.repository')
            ->findUserByEmail($email);

        if (null === $user) {
            throw new \InvalidArgumentException(sprintf('User "%s" does not exist', $email));
        }

        $secret = $user->getPassword();

        $passwordDigest = base64_encode(
            sha1(
                sprintf(
                    '%s%s%s',
                    base64_decode($nonce),
                    $created,
                    $secret
                ),
                true
            )
        );

        return array(
            'HTTP_X-WSSE' => sprintf(
                'UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $email,
                $passwordDigest,
                $nonce,
                $created
            )
        );
    }
}
