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
namespace Diamante\DeskBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterNotifiersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('diamante.ticket_subscriber')) {
            return;
        }

        $definition = $container->getDefinition('diamante.ticket_subscriber');

        $services = $container->findTaggedServiceIds(
            'diamante.ticket_updated_mail_notifier');

        foreach ($services as $id => $attributes) {
            $definition->addMethodCall('registerTicketWasUpdatedNotifiers', array(new Reference($id)));
        }

        $services = $container->findTaggedServiceIds(
            'diamante.ticket_created_mail_notifier');

        foreach ($services as $id => $attributes) {
            $definition->addMethodCall('registerTicketWasCreatedNotifiers', array(new Reference($id)));
        }
    }
}