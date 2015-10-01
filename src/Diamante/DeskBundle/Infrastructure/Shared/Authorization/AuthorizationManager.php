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
namespace Diamante\DeskBundle\Infrastructure\Shared\Authorization;

use Diamante\UserBundle\Entity\ApiUser;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService;
use Diamante\DeskBundle\Model\Shared\Authorization\Authorization;

class AuthorizationManager implements AuthorizationService
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var string
     */
    private $userType;

    /**
     * @var Authorization
     */
    private $authImpl;

    public function __construct(ContainerInterface $serviceContainer,
                                SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
        $user = $this->getLoggedUser();

        switch (true) {
            case ($user instanceof ApiUser):
                $this->authImpl = $serviceContainer->get('diamante.diamante_authorization.service');
                $this->userType = 'Diamante';
                break;
            case ($user instanceof User):
                $this->authImpl = $serviceContainer->get('diamante.oro_authorization.service');
                $this->userType = 'Oro';
                break;
            default:
                $this->authImpl = $serviceContainer->get('diamante.diamante_authorization.service');
                $this->userType = 'Anonymous';
                break;
        }
    }

    /**
     * Gets logged user object or null
     *
     * @return mixed
     */
    public function getLoggedUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            return null;
        }

        $user = $token->getUser();

        return $user;
    }

    /**
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param $attributes
     * @param $object
     * @return bool
     */
    public function isActionPermitted($attributes, $object)
    {
        $isGranted = $this->authImpl->isGranted($attributes, $object);
        return $isGranted;
    }
} 
