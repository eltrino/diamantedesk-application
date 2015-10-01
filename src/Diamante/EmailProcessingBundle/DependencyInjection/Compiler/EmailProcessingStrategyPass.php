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
namespace Diamante\EmailProcessingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class EmailProcessingStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('diamante.email_processing.strategy_holder')) {
            return;
        }

        $taggedServiceHolder = $container->getDefinition('diamante.email_processing.strategy_holder');

        $taggedStrategyServices = $container->findTaggedServiceIds(
            'email_processing.strategy');

        foreach ($taggedStrategyServices as $id => $attributes) {
            $taggedServiceHolder->addMethodCall('addStrategy', array(new Reference($id)));
        }
    }
}
