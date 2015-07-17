<?php

namespace Diamante\DeskBundle\Report;

use Diamante\DeskBundle\Report\ChartType\AbstractChart;

class ChartTypeProvider
{
    const CHART_TYPE_CLASS_PREFIX = 'Diamante\DeskBundle\Report\ChartType';

    /**
     * Relation between config alias and chart class
     *
     * @var array
     */
    protected $charts = [
        'line-chart'        => 'LineChart',
        'bar-chart'         => 'BarChart',
        'grouped-bar-chart' => 'GroupedBarChart',
    ];

    /**
     * @param $alias string
     * @return AbstractChart
     */
    public function getChartTypeObject($alias)
    {
        if (!isset($this->charts[$alias])) {
            throw new \RuntimeException('Chart type not defined');
        }

        $className = static::CHART_TYPE_CLASS_PREFIX . '\\' . $this->charts[$alias];

        if (!class_exists($className)) {
            throw new \RuntimeException('Chart type class not found');
        }

        return new $className;

    }

}