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

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Diamante\DistributionBundle\Routing\VoterProvider;
use Diamante\DistributionBundle\Routing\Voter;

/**
 * Class ShortcutListener
 *
 * @package Diamante\DistributionBundle\EventListener
 */
class ShortcutListener
{
    /**
     * @var array
     */
    protected $whitelist;

    /**
     * @param VoterProvider      $provider
     */
    public function __construct(VoterProvider $provider)
    {
        $this->whitelist = $provider->getCumulativeWhitelist();
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $_route = $event->getRequest()->attributes->get('_route');
        if ('oro_shortcut_actionslist' == $_route) {
            $controllerResult = $event->getControllerResult();
            foreach ($controllerResult['actionsList'] as $route => $data) {
                if (!$this->isTargetWhitelisted($route)) {
                    unset($controllerResult['actionsList'][$route]);
                }
            }

            $event->setControllerResult($controllerResult);
        }
    }

    /**
     * @param $route
     *
     * @return bool
     */
    protected function isTargetWhitelisted($route)
    {
        return in_array($route, $this->whitelist[Voter::TYPE_SHORTCUT]);
    }
}
