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

namespace Diamante\AutomationBundle\DependencyInjection\Compiler;

use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LoadConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('diamante_automation.config.provider')) {
            throw new InvalidConfigurationException("Configuration provider is absent");
        }

        $provider = $container->getDefinition('diamante_automation.config.provider');

        $schema = AutomationConfigurationProvider::getConfigStructure();

        $config = [];

        foreach ($schema as $section) {
            $config[$section] = $container->getParameter(sprintf('diamante.automation.config.%s', $section));
        }

        $provider->addMethodCall('setConfiguration', ['config' => $config]);
    }
}