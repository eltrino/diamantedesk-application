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
namespace Eltrino\DiamanteDeskBundle\Ticket\Infrastructure\Filters;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;

class FilterUrlGeneratorFactory
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var string
     */
    private $defaultPerPage;

    /**
     * @var string;
     */
    private $userId;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->defaultPerPage = $this->container->get('oro_config.global')
            ->get('oro_data_grid.default_per_page');
        $this->userId = $this->getCurrentUserId();
    }

    /**
     * @return User ID
     */
    private function getCurrentUserId()
    {
        $token = $this->container->get('security.context')->getToken();
        return $token ? $token->getUser()->getId() : null;
    }

    /**
     * @return AllTicketsFilterUrlGenerator
     */
    public function createAllTicketsFilterUrlGenerator()
    {
        return new AllTicketsFilterUrlGenerator($this->defaultPerPage, $this->userId);
    }

    /**
     * @return MyTicketsFilterUrlGenerator
     */
    public function createMyTicketsFilterUrlGenerator()
    {
        return new MyTicketsFilterUrlGenerator($this->defaultPerPage, $this->userId);
    }

    /**
     * @return MyNewTicketsFilterUrlGenerator
     */
    public function createMyNewTicketsFilterUrlGenerator()
    {
        return new MyNewTicketsFilterUrlGenerator($this->defaultPerPage, $this->userId, STATUS::NEW_ONE);
    }

    /**
     * @return MyOpenTicketsFilterUrlGenerator
     */
    public function createMyOpenTicketsFilterUrlGenerator()
    {
        return new MyOpenTicketsFilterUrlGenerator($this->defaultPerPage, $this->userId, STATUS::OPEN);
    }

    /**
     * @return MyReportedTicketsFilterUrlGenerator
     */
    public function createMyReportedTicketsFilterUrlGenerator()
    {
        return new MyReportedTicketsFilterUrlGenerator($this->defaultPerPage, $this->userId);
    }

    /**
     * @return MyReportedNewTicketsFilterUrlGenerator
     */
    public function createMyReportedNewTicketsFilterUrlGenerator()
    {
        return new MyReportedNewTicketsFilterUrlGenerator($this->defaultPerPage, $this->userId, STATUS::NEW_ONE);
    }
} 