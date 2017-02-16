<?php

namespace Diamante\DistributionBundle\DependencyInjection;

use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DiamanteDistributionExtension extends Extension implements PrependExtensionInterface
{
    const ORO_NOTIFICATION = 'oro_notification';

    public function prepend(ContainerBuilder $container)
    {
        $loader = new CumulativeConfigLoader(
            'diamante_distribution',
            new YamlCumulativeFileLoader('Resources/config/whitelist.yml')
        );

        $resources = $loader->load();

        $this->populateWhitelist($container, $resources);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $oroNotificationConfiguration = new OroNotificationConfiguration();
        $oroNotificationConfig = $this->processConfiguration($oroNotificationConfiguration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        /**
         * @TODO ORO 2.0
         *   [Symfony\Component\Config\Definition\Exception\InvalidConfigurationException]
         *   The system configuration variable "oro_notification.mass_notification_template" is not defined. Please make sure that it is either added to bundle configuration settings or marked as "ui_only" in config.
         */
//        $container->prependExtensionConfig($this->getAlias(), array_intersect_key($config, array_flip(['settings'])));
//        $container->prependExtensionConfig(
//            static::ORO_NOTIFICATION,
//            array_intersect_key($oroNotificationConfig, array_flip(['settings']))
//        );
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
