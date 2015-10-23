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

namespace Diamante\DistributionBundle\DependencyInjection\Compiler;


use Diamante\DistributionBundle\Dashboard\ConfigProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DisableWidgetsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $widgetConfigProviderId = 'oro_dashboard.config_provider';

        if (!$container->getDefinition($widgetConfigProviderId)) {
            return;
        }

        $widgetConfigProvider = $container->getDefinition($widgetConfigProviderId);

        $widgetConfigProvider->setClass(ConfigProvider::getClass());
    }
}