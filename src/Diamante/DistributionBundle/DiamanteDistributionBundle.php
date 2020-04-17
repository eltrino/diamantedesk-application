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
namespace Diamante\DistributionBundle;

use Diamante\DistributionBundle\DependencyInjection\Compiler\DisableWidgetsPass;
use Diamante\DistributionBundle\DependencyInjection\Compiler\RoutingVotersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DiamanteDistributionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RoutingVotersPass());
        $container->addCompilerPass(new DisableWidgetsPass());
    }

    public function getParent()
    {
        return 'OroInstallerBundle';
    }
}
