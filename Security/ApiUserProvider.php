<?php

namespace Diamante\ApiBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Diamante\ApiBundle\Model\ApiUser\ApiUserRepository;
use Diamante\ApiBundle\Model\ApiUser\ApiUser;

class ApiUserProvider implements UserProviderInterface
{
    /**
     * @var ApiUserRepository
     */
    private $apiUserRepository;

    public function __construct(ApiUserRepository $apiUserRepository)
    {
        $this->apiUserRepository = $apiUserRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        $user = $this->apiUserRepository->findUserByUsername($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
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

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Diamante\ApiBundle\Model\ApiUser\ApiUser';
    }
}
