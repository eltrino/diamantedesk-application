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

namespace Diamante\DistributionBundle\EventListener;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

/**
 * Class ShortcutListener
 *
 * @package Diamante\DistributionBundle\EventListener
 */
class LaunchpadListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');

        if ($route !== 'oro_default') {
            return;
        }

        if (!$this->getUser()) {
            return;
        }

        $dashboard = $this->getDashboardManager()->findUserActiveOrDefaultDashboard($this->getUser());

        if (!$dashboard || !$this->getSecurityFacade()->isGranted('VIEW', $dashboard->getEntity())) {
            $url = $this->getRouter()->generate('diamante_ticket_list');
            $event->setResponse(new RedirectResponse($url));
        }
    }

    /**
     * @return bool|mixed
     */
    protected function getUser()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return false;
        }

        if (!is_object($user = $token->getUser())) {
            return false;
        }

        return $user;
    }

    /**
     * @return \Oro\Bundle\DashboardBundle\Model\Manager
     */
    protected function getDashboardManager()
    {
        return $this->container->get('oro_dashboard.manager');
    }

    /**
     * @return SecurityFacade
     */
    protected function getSecurityFacade()
    {
        return $this->container->get('oro_security.security_facade');
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouter()
    {
        return $this->container->get('router');
    }
}
