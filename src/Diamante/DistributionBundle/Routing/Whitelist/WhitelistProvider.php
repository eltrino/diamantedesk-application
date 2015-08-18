<?php

namespace Diamante\DistributionBundle\Routing\Whitelist;


use Diamante\DistributionBundle\Routing\Whitelist\Specification\WhitelistVotingSpecificationInterface;

interface WhitelistProvider
{
    /**
     * @param WhitelistVotingSpecificationInterface $specification
     */
    public function addWhitelistVotingSpecification(WhitelistVotingSpecificationInterface $specification);

    /**
     * @param $item
     * @return bool
     */
    public function isItemWhitelisted($item);
}