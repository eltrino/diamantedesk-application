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

namespace Diamante\ApiBundle\Routine\Tests\EventListener;

use Diamante\UserBundle\Entity\ApiUser;
use Oro\Bundle\DataAuditBundle\EventListener\EntityListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DataAuditListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param ContainerInterface $container
     * @param TokenStorageInterface     $tokenStorage
     */
    public function __construct(ContainerInterface $container, TokenStorageInterface $tokenStorage)
    {
        $this->container    = $container;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $env  = $this->container->getParameter("kernel.environment");
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return;
        }

        $user = $token->getUser();

        if ('test' == $env && $user instanceof ApiUser) {
            $em           = $this->container->get('doctrine.orm.entity_manager');
            $eventManager = $em->getEventManager();
            foreach ($eventManager->getListeners()['onFlush'] as $hash => $listener) {
                if ($listener instanceof EntityListener) {
                    $eventManager->removeEventListener('onFlush', $listener);
                }
            }
        }
    }
}
