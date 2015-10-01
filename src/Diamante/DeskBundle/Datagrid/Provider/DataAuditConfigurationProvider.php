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
namespace Diamante\DeskBundle\Datagrid\Provider;

use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProvider;

class DataAuditConfigurationProvider implements ConfigurationProviderInterface
{
    const GRID_NAME = 'audit-grid';
    const RESULT_SERVICE = 'diamante_combined_audit_datasource';

    /**
     * @var ConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @param ConfigurationProvider $configurationProvider
     */
    public function __construct(ConfigurationProvider $configurationProvider)
    {
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable($gridName)
    {
        return self::GRID_NAME == $gridName;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration($gridName)
    {
        $configuration = $this->configurationProvider->getConfiguration(self::GRID_NAME);
        $configuration->offsetSetByPath('[source][type]', self::RESULT_SERVICE);

        return $configuration;
    }
}
