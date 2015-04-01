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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository;

class DiamanteAuthorizationImpl implements Authorization
{
    use AuthorizationImplTrait;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var DoctrineDiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var array
     */
    private $permissionsMap
        = array(
            'Diamante\DeskBundle\Entity\Ticket'  => array('VIEW', 'EDIT'),
            'Entity:DiamanteDeskBundle:Ticket'   => array('VIEW', 'CREATE'),
            'Entity:DiamanteDeskBundle:Comment'  => array('CREATE'),
            'Diamante\DeskBundle\Entity\Comment' => array('VIEW', 'EDIT', 'DELETE'),
        );

    /**
     * @param DoctrineDiamanteUserRepository $diamanteUserRepository
     * @param SecurityContextInterface       $securityContext
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        DoctrineDiamanteUserRepository $diamanteUserRepository
    ) {
        $this->securityContext = $securityContext;
        $this->diamanteUserRepository = $diamanteUserRepository;
    }
} 