<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
 *
 * Licensed under the Open Software License (OSL 3.0).
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@eltrino.com so we can send you a copy immediately.
 */

namespace Diamante\AutomationBundle;

use Diamante\AutomationBundle\DependencyInjection\Compiler\RegisterEmailNotifiersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class DiamanteAutomationBundle
 *
 * @package Diamante\AutomationBundle
 */
class DiamanteAutomationBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle\Bundle::build()
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterEmailNotifiersPass());
    }
}
