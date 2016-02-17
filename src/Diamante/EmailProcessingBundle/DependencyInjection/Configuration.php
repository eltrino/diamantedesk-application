<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
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
namespace Diamante\EmailProcessingBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
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
        $rootNode = $treeBuilder->root('diamante_email_processing');

        SettingsBuilder::append(
            $rootNode,
            array(
                'mailbox_server_address' => array(
                    'value' => '',
                    'type' => 'scalar'
                ),
                'mailbox_port' => array(
                    'value' => '',
                    'type' => 'scalar'
                ),
                'mailbox_ssl'     => array('value' => false, 'type' => 'bool'),
                'mailbox_username' => array(
                    'value' => '',
                    'type' => 'scalar'
                ),
                'mailbox_password' => array(
                    'value' => '',
                    'type' => 'password'
                )
            )
        );

        return $treeBuilder;
    }
}
