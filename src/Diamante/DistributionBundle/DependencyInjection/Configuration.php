<?php

namespace Diamante\DistributionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;

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
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('diamante_distribution');

        SettingsBuilder::append(
            $rootNode,
            [
                'organization_name'  => ['value' => 'DiamanteDesk'],
                'application_url'    => ['value' => 'http://localhost/diamantedesk/']
            ]
        );

        return $treeBuilder;
    }
}
