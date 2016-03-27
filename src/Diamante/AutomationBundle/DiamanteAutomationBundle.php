<?php

namespace Diamante\AutomationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DiamanteAutomationBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle\Bundle::build()
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new \Diamante\AutomationBundle\DependencyInjection\Compiler\RegisterEmailProvidersPass());
    }
}
