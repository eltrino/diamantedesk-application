<?php

namespace Diamante\ApiBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestClientCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('test.client')) {
            $definition = $container->getDefinition('test.client');
            $definition->setClass('Symfony\Bundle\FrameworkBundle\Client');
        }
    }
}