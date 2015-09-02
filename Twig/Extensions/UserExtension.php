<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\UserBundle\Twig\Extensions;


use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineTicketRepository;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineApiUserRepository;
use Diamante\UserBundle\Infrastructure\Persistence\Doctrine\DoctrineDiamanteUserRepository;
use Diamante\UserBundle\Model\User;

class UserExtension extends \Twig_Extension
{
    /**
     * @var DoctrineDiamanteUserRepository
     */
    protected $diamanteUserRepository;

    /**
     * @var DoctrineApiUserRepository
     */
    protected $apiUserRepository;

    /**
     * @var DoctrineTicketRepository
     */
    protected $ticketRepository;

    public function __construct(
        DoctrineDiamanteUserRepository $diamanteUserRepository,
        DoctrineApiUserRepository $apiUserRepository,
        DoctrineTicketRepository $ticketRepository
    )
    {
        $this->diamanteUserRepository = $diamanteUserRepository;
        $this->apiUserRepository      = $apiUserRepository;
        $this->ticketRepository       = $ticketRepository;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'diamante.user.extension';
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('user_has_tickets', [$this, 'userHasTickets'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('user_has_api_user', [$this, 'userHasApiUserConnected'], ['is_safe' => ['html']]),
        ];
    }

    public function userHasTickets(DiamanteUser $user)
    {
        $reporter = new User($user->getId(), User::TYPE_DIAMANTE);
        $tickets = $this->ticketRepository->count([['reporter', 'eq', (string)$reporter ]]);

        return $tickets > 0;
    }

    public function userHasApiUserConnected(DiamanteUser $user)
    {

    }
}