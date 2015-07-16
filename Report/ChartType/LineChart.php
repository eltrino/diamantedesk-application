<?php

namespace Diamante\DeskBundle\Report\ChartType;

class LineChart extends AbstractChart
{
    const X_AXIS_ALIAS = 'x-axis';
    const Y_AXIS_ALIAS = 'y-axis';

    /**
     * @param array $records
     * @param $config
     * @return array
     */
    public function extractData(array $records, $config)
    {
        $this->validateParameters($config);

        $xPropertyName = $config['chart'][static::X_AXIS_ALIAS];
        $yPropertyName = $config['chart'][static::Y_AXIS_ALIAS];

        $extractedData = [];
        foreach ($records as $record) {
            $extractedData[] = [
                'x' => is_object($record[$xPropertyName]) ? (string)$record[$xPropertyName] : $record[$xPropertyName],
                'y' => is_object($record[$yPropertyName]) ? (string)$record[$yPropertyName] : $record[$yPropertyName],
            ];
        }

        return $extractedData;
    }

    /**
     * @param array $config
     * @return bool
     */
    protected function validateParameters(array $config)
    {
        if (!isset($config['chart'][static::X_AXIS_ALIAS]) || !isset($config['chart'][static::Y_AXIS_ALIAS])) {
            throw new \RuntimeException("Report has missed required parameters");
        }
        return true;
    }
}