<?php

namespace Diamante\AutomationBundle;

use Diamante\AutomationBundle\DependencyInjection\Compiler\RegisterRulesPass;
use Diamante\AutomationBundle\DependencyInjection\Compiler\RegisterStrategiesPass;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DiamanteAutomationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RegisterRulesPass());
        $container->addCompilerPass(new RegisterStrategiesPass());
    }

    public function boot()
    {
        if (!Type::hasType('target_type')) {
             Type::addType('target_type', 'Diamante\AutomationBundle\Infrastructure\Persistence\Doctrine\DBAL\Types\TargetType');
        }

        if (!Type::hasType('condition_type')) {
            Type::addType('condition_type', 'Diamante\AutomationBundle\Infrastructure\Persistence\Doctrine\DBAL\Types\ConditionType');
        }
    }
}
