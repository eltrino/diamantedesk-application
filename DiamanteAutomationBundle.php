<?php

namespace Diamante\AutomationBundle;

use Diamante\AutomationBundle\DependencyInjection\Compiler\LoadConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DiamanteAutomationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new LoadConfigurationPass());
    }
}
