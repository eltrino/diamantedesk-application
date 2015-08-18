<?php

namespace Diamante\DistributionBundle\DependencyInjection;

use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DiamanteDistributionExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container)
    {
        $loader = new CumulativeConfigLoader(
            'diamante_distribution',
            new YamlCumulativeFileLoader('Resources/config/whitelist.yml')
        );

        $resources  = $loader->load();

        $this->populateWhitelist($container, $resources);
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

        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
    }

    private function populateWhitelist(ContainerBuilder $container, $resource)
    {
        if (count($resource) > 1) {
            throw new InvalidArgumentException('Whitelist configuration has to be defined in single file');
        }

        $config = $resource[0];
        $rules = $config->data['whitelist'];

        $container->setParameter('diamante.distribution.whitelist.rules', $rules);
    }
}
