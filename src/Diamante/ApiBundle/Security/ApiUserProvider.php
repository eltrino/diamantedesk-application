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

namespace Diamante\ApiBundle\Security;

use Diamante\UserBundle\Entity\ApiUser;
use Diamante\UserBundle\Model\ApiUser\ApiUserRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiUserProvider implements UserProviderInterface
{
    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    public function __construct(ApiUserRepository $diamanteUserRepository)
    {
        $this->apiUserRepository = $diamanteUserRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->loadUserByEmail($username);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByEmail($email)
    {
        $user = $this->apiUserRepository->findUserByEmail($email);

        if (!$user || (true === $user->getDiamanteUser()->isDeleted())) {
            throw new UsernameNotFoundException(sprintf('User "%s" does not exist.', $email));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof ApiUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getEmail());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Diamante\ApiBundle\Model\ApiUser\ApiUser';
    }
}
