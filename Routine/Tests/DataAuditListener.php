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

namespace Diamante\ApiBundle\Routine\Tests;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use \Oro\Bundle\DataAuditBundle\EventListener\EntityListener;
use Diamante\UserBundle\Entity\ApiUser;

class DataAuditListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    /**
     * @param ContainerInterface $container
     * @param SecurityFacade     $securityFacade
     */
    public function __construct(ContainerInterface $container, SecurityFacade $securityFacade)
    {
        $this->container      = $container;
        $this->securityFacade = $securityFacade;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $env = $this->container->getParameter("kernel.environment");
        $user = $this->securityFacade->getLoggedUser();

        if ('test' == $env && $user instanceof ApiUser) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $eventManager = $em->getEventManager();
            foreach ($eventManager->getListeners()['onFlush'] as $hash => $listener) {
                if ($listener instanceof EntityListener) {
                    $eventManager->removeEventListener('onFlush', $listener);
                }
            }
        }
    }
}
