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
namespace Diamante\ApiBundle\Tests\Security\Firewall;

use Diamante\ApiBundle\Security\Authentication\Token\WsseToken;
use Diamante\ApiBundle\Security\Firewall\WsseListener;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class WsseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\ApiBundle\Security\Firewall\WsseListener
     */
    private $wsseListener;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     * @Mock \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    private $authenticationManager;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     * @Mock \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     * @Mock \Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    private $tokenMock;

    /**
     * @var \Symfony\Component\HttpKernel\Event\GetResponseEvent
     * @Mock \Symfony\Component\HttpKernel\Event\GetResponseEvent
     */
    private $responseEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     * @Mock \Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     * @Mock \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Diamante\UserBundle\Entity\ApiUser
     * @Mock \Diamante\UserBundle\Entity\ApiUser
     */
    private $userMock;

    /**
     * @var \JMS\Serializer\Serializer
     * @Mock \JMS\Serializer\Serializer
     */
    private $serializer;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->request = $this->getMockForAbstractClass('Symfony\Component\HttpFoundation\Request');

        $this->responseEvent
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->wsseListener = new WsseListener($this->serializer, $this->securityContext, $this->authenticationManager, $this->logger);
    }

    /**
     * @test
     */
    public function handleReturnToken()
    {
        $token = new WsseToken();
        $token->setUser('admin');
        $token->setAttribute('digest','admin');
        $token->setAttribute('nonce','admin');
        $token->setAttribute('created','2010-12-12 20:00:00');

        $this->tokenMock
            ->expects($this->atLeastOnce())
            ->method('getUser')
            ->will($this->returnValue($this->userMock));

        $this->userMock
            ->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue(true));

        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($token)
            ->will($this->returnValue($this->tokenMock));

        /** @noinspection PhpUndefinedMethodInspection */
        $this->securityContext
            ->expects($this->once())
            ->method('setToken')
            ->with($this->tokenMock);

        $this->request->headers
            ->add(array('X-WSSE'=>'UsernameToken Username="admin", PasswordDigest="admin", Nonce="admin", Created="2010-12-12 20:00:00"'));

        $this->wsseListener->handle($this->responseEvent);
    }

    /**
     * @test
     */
    public function handleReturnResponse()
    {
        $token = new WsseToken();
        $token->setUser('admin');
        $token->setAttribute('digest','admin');
        $token->setAttribute('nonce','admin');
        $token->setAttribute('created','2010-12-12 20:00:00');

        $this->authenticationManager
            ->expects($this->once())
            ->method('authenticate')
            ->with($token)
            ->will($this->returnValue($this->response));

        $this->responseEvent
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->response);

        $this->request->headers
            ->add(array('X-WSSE'=>'UsernameToken Username="admin", PasswordDigest="admin", Nonce="admin", Created="2010-12-12 20:00:00"'));

        $this->wsseListener->handle($this->responseEvent);
    }
} 