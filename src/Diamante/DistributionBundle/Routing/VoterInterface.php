<?php

namespace Diamante\DistributionBundle\Routing;

interface VoterInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getListedItems();
}