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
namespace Diamante\ApiBundle\Security\Firewall;

use Diamante\ApiBundle\Security\Authentication\Token\WsseToken;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class WsseListener implements ListenerInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager
    )
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $wsseRegex = '/UsernameToken Username="([^"]+)", PasswordDigest="([^"]+)", Nonce="([^"]+)", Created="([^"]+)"/';

        if (!$request->headers->has('x-wsse') ||
            1 !== preg_match($wsseRegex, $request->headers->get('x-wsse'), $matches))
        {
            return;
        }

        $token = new WsseToken();
        $token->setUser($matches[1]);

        $token->setAttribute('digest', $matches[2]);
        $token->setAttribute('nonce', $matches[3]);
        $token->setAttribute('created', $matches[4]);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface)
            {
                if (!$returnValue->getUser()->isActive()) {
                    throw new AuthenticationException();
                }

                return $this->securityContext->setToken($returnValue);
            }
            else if ($returnValue instanceof Response)
            {
                return $event->setResponse($returnValue);
            }

        } catch (AuthenticationException $failed) {
            // ... you might log something here

            // To deny the authentication clear the token. This will redirect to the login page.
            // Make sure to only clear your token, not those of other authentication listeners.
            // $token = $this->securityContext->getToken();
            // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
            //     $this->securityContext->setToken(null);
            // }
            // return;
            // By default deny authorization
            // $response = new Response();
            // $response->setStatusCode(403);
            // $event->setResponse($response);
        }
    }
}
