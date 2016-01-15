<?php

namespace Diamante\AutomationBundle\DependencyInjection;

use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Configuration\ConfigCacheDumper;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class DiamanteAutomationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('forms.xml');

        $this->loadAutomationConfig($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function loadAutomationConfig(ContainerBuilder $container)
    {
        $config = $this->getConfig($container);
        $schema = AutomationConfigurationProvider::getConfigStructure();

        foreach ($schema as $section) {
            if (array_key_exists($section, $config)) {
                $container->setParameter(sprintf('diamante.automation.config.%s', $section), $config[$section]);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @return array|mixed
     */
    protected function getConfig(ContainerBuilder $container)
    {
        $cache = new ConfigCache(
            sprintf(
                "%s/cache/%s/automation_config.php",
                $container->getParameter('kernel.root_dir'),
                $container->getParameter('kernel.environment')
            ),
            $container->getParameter('kernel.environment') === 'dev' ? true : false
        );

        if (!$cache->isFresh()) {
            $config = $this->doLoadConfig();
            $cache->write(ConfigCacheDumper::dump($config));
        }

        $config = require $cache;

        return $config;
    }

    /**
     * @return array
     */
    protected function doLoadConfig()
    {
        $loader = new CumulativeConfigLoader(
            'diamante_automation',
            new YamlCumulativeFileLoader('Resources/config/automation.yml')
        );

        $resources = $loader->load();

        $schema = AutomationConfigurationProvider::getConfigStructure();

        foreach ($schema as $section) {
            $config[$section] = [];
        }

        foreach ($resources as $resource) {
            $config = array_merge_recursive($config, $resource->data['diamante_automation']);
        }

        return $config;
    }
}
