<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Oro\Bundle\DistributionBundle\OroKernel;

class AppKernel extends OroKernel
{
    public function registerBundles()
    {
        $bundles = [];

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
        }

        if ('dev' === $this->getEnvironment()
            && class_exists('Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle')
        ) {
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        if ('test' === $this->getEnvironment()) {
            $bundles[] = new Oro\Bundle\TestFrameworkBundle\OroTestFrameworkBundle();
        }

        $bundles[] = new JMS\JobQueueBundle\JMSJobQueueBundle();

        return array_merge(parent::registerBundles(), $bundles);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
