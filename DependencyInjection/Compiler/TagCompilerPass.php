<?php

namespace Diamante\DeskBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TagCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceId = 'oro_tag.tag.manager';
        if ($container->has($serviceId)) {
            $definition = $container->getDefinition($serviceId);
            $definition->setClass('Diamante\DeskBundle\Infrastructure\Tag\TagManager');
        }
    }
}