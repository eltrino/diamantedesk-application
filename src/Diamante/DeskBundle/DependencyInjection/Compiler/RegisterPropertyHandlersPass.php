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

namespace Diamante\DeskBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterPropertyHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('diamante_desk.entity.property_processing_manager')) {
            return;
        }

        $resolver = $container->getDefinition('diamante_desk.entity.property_processing_manager');
        $strategies = $container->findTaggedServiceIds('diamante_desk.property.handler');

        foreach ($strategies as $id => $definition) {
            $resolver->addMethodCall('addPropertyHandler', [new Reference($id)]);
        }
    }
}