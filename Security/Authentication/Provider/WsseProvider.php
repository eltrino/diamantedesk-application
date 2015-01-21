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
namespace Diamante\ApiBundle\Security\Authentication\Provider;

use Diamante\ApiBundle\Security\Authentication\Token\WsseToken;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class WsseProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    /**
     * @var Cache
     */
    private $nonceCache;

    /**
     * Token lifetime in seconds
     * @var string
     */
    private $lifetime;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @param UserProviderInterface $userProvider
     * @param Cache $nonceCache
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function __construct(UserProviderInterface $userProvider, Cache $nonceCache, EncoderFactoryInterface $encoderFactory)
    {
        $this->userProvider   = $userProvider;
        $this->nonceCache     = $nonceCache;
        $this->encoderFactory = $encoderFactory;
        $this->lifetime       = 300;
    }

    /**
     * @param TokenInterface $token
     * @return WsseToken|TokenInterface
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user && $this->validateDigest(
                $token->getAttribute('digest'),
                $token->getAttribute('nonce'),
                $token->getAttribute('created'),
                $this->getSecret($user),
                $this->getSalt($user),
                $user
                )
            )
            {
            $authenticatedToken = new WsseToken($user->getRoles());
            $authenticatedToken->setUser($user);
            $authenticatedToken->setAuthenticated(true);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The WSSE authentication failed.');
    }

    /**
     * @param $digest
     * @param $nonce
     * @param $created
     * @param $secret
     * @param $salt
     * @param UserInterface $user
     * @return bool
     */
    private function validateDigest($digest, $nonce, $created, $secret, $salt, UserInterface $user)
    {
        // Check created time is not in the future
        if ($this->isTokenFromFuture($created))
        {
            throw new BadCredentialsException('Future token detected.');
        }

        // Expire timestamp after token lifetime
        if (time() - strtotime($created) > $this->lifetime) {
            throw new CredentialsExpiredException('Token has expired.');
        }

        //validate that nonce is unique within specified lifetime
        //if it is not, this could be a replay attack
        if($this->nonceCache->contains($nonce))
        {
            throw new NonceExpiredException('Previously used nonce detected.');
        }

        $this->nonceCache->save($nonce, time(), $this->lifetime);

        //validate secret
        $expected = $this->getEncoder($user)->encodePassword(
            sprintf(
                '%s%s%s',
                base64_decode($nonce),
                $created,
                $secret
            ),
            $salt
        );

        return $digest === $expected;
    }

    /**
     * @param $user
     * @return \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface|void
     */
    private function getEncoder($user)
    {
        return $this->encoderFactory->getEncoder($user);
    }

    /**
     * @param UserInterface $user
     * @return string
     */
    private function getSecret(UserInterface $user)
    {
        $encoder = $this->getEncoder($user);
        return $encoder->encodePassword($user->getPassword(), $user->getSalt());
    }

    /**
     * @param UserInterface $user
     * @return null|string
     */
    private function getSalt(UserInterface $user)
    {
        return $user->getSalt();
    }

    /**
     * @param $created
     * @return bool
     */
    private function isTokenFromFuture($created)
    {
        return strtotime($created) > time();
    }

    /**
     * @param TokenInterface $token
     * @return bool
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof WsseToken;
    }
}