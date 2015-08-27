<?php

namespace Diamante\AutomationBundle\DependencyInjection;

use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DiamanteAutomationExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $loader = new CumulativeConfigLoader(
            'diamante_automation',
            new YamlCumulativeFileLoader('Resources/config/automation.yml')
        );

        $resources  = $loader->load();

        $this->populateAutomationEntities($container, $resources);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('actions.xml');
        $loader->load('strategies.xml');
    }

    private function populateAutomationEntities(ContainerBuilder $container, $resource)
    {
        $entities = [];
        foreach ($resource as $item) {
            if (isset($item->data['entities'])) {
                $entities = array_merge($entities, $item->data['entities']);
            }
        }

        $container->setParameter('diamante.automation.listed_entities', $entities);
    }
}
