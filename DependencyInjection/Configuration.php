<?php

namespace Diamante\AutomationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('diamante_automation');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode->children()
            ->arrayNode('entities')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('frontend_label')->end()
                        ->arrayNode('properties')
                            ->children()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('actions')
                ->prototype('array')
                    ->children()
                        ->scalarNode('frontend_label')->end()
                        ->variableNode('frontend_options')->end()
                        ->arrayNode('data_types')->children()->end()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('conditions')
                ->prototype('array')
                    ->children()
                        ->scalarNode('frontend_label')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
