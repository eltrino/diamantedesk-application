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

namespace Diamante\DeskBundle\Report\Infrastructure\Internal;

use Diamante\DeskBundle\Report\Infrastructure\ReportBuilder;
use Diamante\DeskBundle\Report\Infrastructure\ReportService;
use Oro\Component\Config\CumulativeResourceInfo;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ReportServiceImpl
 * @package Diamante\DeskBundle\Report\Api\Internal
 */
class ReportServiceImpl implements ReportService
{
    const FILE_PATH = 'Resources/config/reports.yml';

    const CONFIG_ID = 'diamante_report';

    /**
     * @var array
     */
    private $config;

    /**
     * @var ReportBuilder
     */
    protected $reportBuilder;

    public function __construct(
        ReportBuilder $reportBuilder
    ) {
        $this->reportBuilder = $reportBuilder;
        $this->readConfig();
    }

    /**
     * @param null $reportId
     * @return array
     */
    public function getConfig($reportId = null)
    {
        if (!$reportId) {
            return $this->config;
        }

        if (isset($this->config[$reportId])) {
            return $this->config[$reportId];
        }

        return [];
    }

    /**
     * @return $this
     */
    public function readConfig()
    {
        if (!empty($this->config)) {
            return $this->getConfig();
        }

        $builder = new ContainerBuilder();

        $configLoader = new CumulativeConfigLoader(
            static::CONFIG_ID,
            new YamlCumulativeFileLoader(static::FILE_PATH)
        );

        $resources = $configLoader->load($builder);

        $config = [];

        /** @var CumulativeResourceInfo $resourceInfo */
        foreach ($resources as $resourceInfo) {
            foreach ($resourceInfo->data['reports'] as $reportId => $reportConfig) {
                $config[$reportId] = $reportConfig;
            }
        }

        $this->config = $config;

        return $this;
    }

    public function build($reportId)
    {
        $config = $this->getConfig($reportId);

        if (empty($config)) {
            throw new \RuntimeException(sprintf("Configuration for report %s not found", $reportId));
        }

        return $this->reportBuilder->build($config, $reportId);
    }
}