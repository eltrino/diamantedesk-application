<?php

namespace Diamante\DistributionBundle;

use Diamante\DistributionBundle\DependencyInjection\Compiler\DisableWidgetsPass;
use Diamante\DistributionBundle\DependencyInjection\Compiler\RoutingVotersPass;
use Diamante\DistributionBundle\DependencyInjection\Compiler\InjectScenarioPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DiamanteDistributionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RoutingVotersPass());
        $container->addCompilerPass(new InjectScenarioPass());
        $container->addCompilerPass(new DisableWidgetsPass());
    }

    public function getParent()
    {
        return 'OroInstallerBundle';
    }
}
