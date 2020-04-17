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

namespace Diamante\DeskBundle\EventListener;

use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DataGridPreBuildListener
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onPreBuild(PreBuild $event)
    {
        /** @var \Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration $config */
        $config = $event->getConfig();
        if ($config->getName() === 'diamante-my-recent-tickets-widget-grid') {
            $parameters = $event->getParameters();
            $currentUserId = $this->getLoggedUser()->getId();
            $parameters->add(
                array('userId' => $currentUserId, 'reporterId' => sprintf("oro_%s", $currentUserId))
            );
        }
        return;
    }

    /**
     * Get logged user or null
     *
     * @return object|null
     */
    private function getLoggedUser()
    {
        /** @var TokenStorageInterface $tokenStorage */
        $tokenStorage = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();

        if (!$token) {
            return null;
        }

        $user = $token->getUser();

        if (!is_object($user)) {
            return null;
        }

        return $user;
    }
}
