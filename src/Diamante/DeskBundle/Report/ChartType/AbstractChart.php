<?php

namespace Diamante\DeskBundle\Report\ChartType;

abstract class AbstractChart
{
    /**
     * @param array $records
     * @param $config
     * @return array
     */
    abstract public function extractData(array $records, $config);

    /**
     * @param array $config
     * @return bool
     */
    abstract protected function validateParameters(array $config);
}