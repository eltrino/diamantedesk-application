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

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use JMS\Serializer\Serializer;
use FOS\RestBundle\Util\Codes;

class WsseListener implements ListenerInterface
{
    protected $serializer;
    protected $securityContext;
    protected $authenticationManager;
    protected $logger;

    public function __construct(
        Serializer $serializer,
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager,
        Logger  $logger
    )
    {
        $this->serializer = $serializer;
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
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

        $user = $matches[1];

        $token = new WsseToken();
        $token->setUser($user);

        $token->setAttribute('digest', $matches[2]);
        $token->setAttribute('nonce', $matches[3]);
        $token->setAttribute('created', $matches[4]);

        try {
            $returnValue = $this->authenticationManager->authenticate($token);

            if ($returnValue instanceof TokenInterface)
            {
                if (!$returnValue->getUser()->isActive()) {
                    throw new AuthenticationException(
                        "Your account is not activated yet, please check your email and confirm registration.\n" .
                        "If you didn't receive your verification email, please <a href=\"#reconfirm/$user\">click here.</a>");
                    }

                return $this->securityContext->setToken($returnValue);
            } else if ($returnValue instanceof Response)
            {
                $event->setResponse($returnValue);
                return;
            }

        } catch (AuthenticationException $failed) {
            $this->logger->error(sprintf("Authentication failed for user %s. Reason: %s", $token->getUser(), $failed->getMessage()));
            $response = new Response(
                $this->serializer->serialize(['message' => $failed->getMessage()],
                    $request->getRequestFormat()), Codes::HTTP_UNAUTHORIZED
            );
            $event->setResponse($response);
        }
    }
}
