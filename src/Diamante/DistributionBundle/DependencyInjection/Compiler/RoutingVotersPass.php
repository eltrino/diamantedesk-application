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

namespace Diamante\DistributionBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RoutingVotersPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     * @return bool
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('diamante.distribution.whitelist.provider')) {
            return false;
        }

        $def = $container->getDefinition('diamante.distribution.whitelist.provider');

        foreach ($container->findTaggedServiceIds('diamante.routing.spec') as $id => $attrs) {
            $def->addMethodCall('addWhitelistVotingSpecification', [new Reference($id)]);
        }
    }
}