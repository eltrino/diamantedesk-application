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

namespace Diamante\DistributionBundle\Routing;


class RoutingVoterProvider implements VoterProvider
{
    /**
     * @var array
     */
    protected $voters = [];
    /**
     * @var array
     */
    protected $whitelist = [];

    /**
     * @param \Diamante\DistributionBundle\Routing\Voter $voter
     */
    public function addVoter(Voter $voter)
    {
        $oid = spl_object_hash($voter);
        if (!array_key_exists($oid, $this->voters)) {
            $this->voters[$oid] = $voter;
            $this->rebuildWhitelist($voter);
        }
    }

    /**
     * @return array
     */
    public function getCumulativeWhitelist()
    {
        return $this->whitelist;
    }

    /**
     * @param \Diamante\DistributionBundle\Routing\Voter $voter
     */
    protected function rebuildWhitelist(Voter $voter)
    {
        if (!array_key_exists($voter->getType(), $this->whitelist)) {
            $this->whitelist[$voter->getType()] = $voter->getListedItems();
            return;
        }

        $this->whitelist[$voter->getType()] = array_merge($this->whitelist[$voter->getType()], $voter->getListedItems());
    }
}