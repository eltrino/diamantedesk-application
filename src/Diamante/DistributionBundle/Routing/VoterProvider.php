<?php

namespace Diamante\DistributionBundle\Routing;


interface VoterProvider
{
    /**
     * @param \Diamante\DistributionBundle\Routing\Voter $voter
     * @return void
     */
    public function addVoter(Voter $voter);

    /**
     * @return array
     */
    public function getCumulativeWhitelist();
}