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

use Diamante\DeskBundle\Report\ChartTypeProvider;
use Diamante\DeskBundle\Report\Infrastructure\ReportBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\QueryException;


/**
 * Class ReportServiceImpl
 * @package Diamante\DeskBundle\Report\Api\Internal
 */
class ReportBuilderImpl implements ReportBuilder
{
    const TYPE_DQL = 'dql';

    const TYPE_REPOSITORY = 'repository';

    const CALLABLE_PREFIX = 'buildFrom';

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ChartTypeProvider
     */
    protected $chartTypeProvider;

    public function __construct(
        EntityManager $entityManager,
        ChartTypeProvider $chartTypeProvider
    ) {
        $this->entityManager = $entityManager;
        $this->chartTypeProvider = $chartTypeProvider;
    }

    /**
     * @param $config
     * @param $reportId
     * @return mixed
     */
    public function build($config, $reportId)
    {

        $callable = $this->resolveSourceResultMethod($config['source'], $reportId);

        if (is_callable(array($this, $callable))) {
            $result = $this->$callable($config['source']);
        } else {
            throw new \RuntimeException();
        }

        if (empty($result)) {
            return [];
        }

        $chart = $this->chartTypeProvider->getChartTypeObject($config['chart']['type']);
        return $chart->extractData($result, $config);

    }

    /**
     * Retrieve callable interface for getting results
     *
     * @param $config
     * @param $reportId
     * @return array
     */
    private function resolveSourceResultMethod($config, $reportId)
    {

        if (!isset($config['type'])) {
            $config['type'] = static::TYPE_DQL;
        }


        if ($config['type'] == static::TYPE_DQL) {
            if (!isset($config['dql'])) {
                $message = sprintf("Parameter 'dql' is not defined in source for report %s", $reportId);
                throw new \RuntimeException($message);
            }
            $callable = static::CALLABLE_PREFIX . ucfirst(static::TYPE_DQL);
        }

        if ($config['type'] == static::TYPE_REPOSITORY) {
            if (!isset($config['dql'])) {
                $message = sprintf("Parameter 'repository' is not defined in source for report %s", $reportId);
                throw new \RuntimeException($message);
            }
            $callable = static::CALLABLE_PREFIX . ucfirst(static::TYPE_REPOSITORY);
        }

        if (!isset($callable)) {
            $message = sprintf("Unknown source type %s for report %s", $config['type'], $reportId);
            throw new \RuntimeException($message);
        }

        return $callable;
    }

    /**
     * @param $config
     * @return array|mixed
     */
    protected function buildFromDql($config)
    {

        $query = $this->entityManager->createQuery($config['dql']);
        try {
            $result = $query->execute();
        } catch (QueryException $e) {
            return [];
        }

        return $result;
    }

    /**
     * TODO: implement method
     *
     * @param $config
     * @return array
     */
    protected function buildFromRepository($config)
    {
        return [];
    }


}