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

use Diamante\DeskBundle\Model\Shared\Authorization\Authorization;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository;

class DiamanteAuthorizationImpl implements Authorization
{
    use AuthorizationImplTrait;

    /**
     * @param DoctrineDiamanteUserRepository $diamanteUserRepository
     * @param TokenStorageInterface          $tokenStorage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        DoctrineDiamanteUserRepository $diamanteUserRepository
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->diamanteUserRepository = $diamanteUserRepository;

        $this->permissionsMap = array(
            'Diamante\DeskBundle\Entity\Ticket'  => array('VIEW', 'EDIT'),
            'Entity:DiamanteDeskBundle:Ticket'   => array('VIEW', 'CREATE'),
            'Entity:DiamanteDeskBundle:Comment'  => array('CREATE'),
            'Diamante\DeskBundle\Entity\Comment' => array('VIEW', 'EDIT', 'DELETE'),
        );
    }
} 
