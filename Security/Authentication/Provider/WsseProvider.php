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
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class WsseProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $cacheDir;

    /**
     * Token lifetime in seconds
     * @var string
     */
    private $lifetime;

    /**
     * @param UserProviderInterface $userProvider
     * @param $cacheDir
     */
    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {
        $this->userProvider = $userProvider;
        $this->cacheDir     = $cacheDir;
        $this->lifetime     = 300;
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
                $user->getPassword()))
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
     * @return bool
     */
    protected function validateDigest($digest, $nonce, $created, $secret)
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

        // Validate that the nonce is *not* used in the last 5 minutes
        // if it has, this could be a replay attack
        if ($this->nonceIsUsed($nonce))
        {
            throw new NonceExpiredException('Previously used nonce detected');
        }
        // If cache directory does not exist we create it
        // And save cache
        $this->saveNonceInCache($nonce);

        // Validate Secret
        $expected = base64_encode(sha1(base64_decode($nonce) . $created . $secret, true));

        return $digest === $expected;
    }

    /**
     * @param $nonce
     */
    private function saveNonceInCache($nonce)
    {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        file_put_contents($this->cacheDir . '/' . $nonce, time());
    }

    /**
     * @param $nonce
     * @return bool
     */
    private function nonceIsUsed($nonce)
    {
        return
            (file_exists($this->cacheDir.'/' . $nonce) &&
            file_get_contents($this->cacheDir .'/'. $nonce) + $this->lifetime > time());
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