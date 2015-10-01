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
namespace Diamante\UserBundle\Api\Internal;

use Diamante\UserBundle\Api\UserStateService;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\UserBundle\Entity\User;

class UserStateServiceImpl implements UserStateService
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function isOroUser()
    {
        $token = $this->securityContext->getToken();

        if (!$token) {
            return false;
        }

        return $token->getUser() instanceof User;
    }
}