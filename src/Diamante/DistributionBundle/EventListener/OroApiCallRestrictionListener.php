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

use Diamante\DistributionBundle\Routing\Voter;
use Diamante\DistributionBundle\Routing\VoterProvider;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class OroApiCallRestrictionListener
{
    /**
     * @var array
     */
    protected $whitelist;
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @param \Diamante\DistributionBundle\Routing\VoterProvider $provider
     * @param \Symfony\Bridge\Monolog\Logger                     $logger
     */
    public function __construct(VoterProvider $provider, Logger $logger)
    {
        $this->whitelist = $provider->getCumulativeWhitelist();
        $this->logger = $logger;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $route = $request->attributes->get('_route');
        if (empty($route)) {
            $route = $request->attributes->get('_master_request_route');
        }

        if (!$this->isTargetWhitelisted($route)){
            $this->logger->addWarning(sprintf("Route %s doesn't seem to be whitelisted. Please, check the configuration.", $route));
            $event->setResponse(new Response('Access to this resource is restricted', 403));
        }
    }

    /**
     * @param $route
     * @return bool
     */
    protected function isTargetWhitelisted($route)
    {
        if ($this->targetIsInStringWhitelist($route)) {
            return true;
        }

        if ($this->targetIsInRegexpWhitelist($route)) {
            return true;
        }

        return false;
    }

    /**
     * @param $route
     * @return bool
     */
    protected function targetIsInStringWhitelist($route)
    {
        return in_array($route, $this->whitelist[Voter::TYPE_STRING]);
    }

    /**
     * @param $route
     * @return bool
     */
    protected function targetIsInRegexpWhitelist($route)
    {
        foreach ($this->whitelist[Voter::TYPE_REGEXP] as $rule) {
            if (preg_match(sprintf('/%s/', $rule), $route) === 1) {
                return true;
            }
        }

        return false;
    }
}