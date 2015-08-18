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

namespace Diamante\DistributionBundle\Routing\Whitelist;

use Diamante\DistributionBundle\Routing\Whitelist\Specification\WhitelistVotingSpecificationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WhitelistProviderImpl implements WhitelistProvider
{
    const EXTENSION = 'diamante_distribution';

    /**
     * @var WhitelistVotingSpecificationInterface[]
     */
    protected $specifications;

    /**
     * @var array
     */
    protected $whitelist;

    public function __construct(ContainerInterface $container)
    {
        $this->whitelist = $container->getParameter('diamante.distribution.whitelist.rules');
    }

    /**
     * @param WhitelistVotingSpecificationInterface $specification
     */
    public function addWhitelistVotingSpecification(WhitelistVotingSpecificationInterface $specification)
    {
        $this->specifications[$specification->getType()] = $specification;
    }

    /**
     * @param string $item
     * @return bool
     */
    public function isItemWhitelisted($item)
    {
        foreach ($this->whitelist as $type => $values) {
            if (isset($type, $this->specifications)) {
                $spec = $this->specifications[$type];
                $result = $spec->isItemWhitelisted($item, $values);

                if (true === $result) {
                    return true;
                }
            }
        }

        return false;
    }
}