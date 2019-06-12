<?php
/*
 * Copyright (c) 2017 Eltrino LLC (http://eltrino.com)
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

use Diamante\DeskBundle\EventListener\SendChangedEntitiesToMessageQueueListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SendChangedEntitiesToMessageQueueCompilerPass implements CompilerPassInterface
{
    const SERVICE_ID = 'oro_dataaudit.listener.send_changed_entities_to_message_queue';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
//        if ($container->has(self::SERVICE_ID)) {
//            $definition = $container->getDefinition(self::SERVICE_ID);
//            $definition->setClass(SendChangedEntitiesToMessageQueueListener::class);
//        }
    }
}
