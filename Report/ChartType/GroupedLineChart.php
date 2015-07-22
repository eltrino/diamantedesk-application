<?php

namespace Diamante\DeskBundle\Report\ChartType;

class GroupedLineChart extends AbstractChart
{
    const X_AXIS_ALIAS = 'x-axis';
    const Y_AXIS_ALIAS = 'y-axis';
    const Y_ITEMS_GROUP_NAME = 'group';

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
        $groupName = $config['chart'][static::Y_ITEMS_GROUP_NAME];

        foreach ($records as $record) {
            $xData = is_object($record[$xPropertyName]) ? (string)$record[$xPropertyName] : $record[$xPropertyName];
            $yData = is_object($record[$yPropertyName]) ? (string)$record[$yPropertyName] : $record[$yPropertyName];
            $groupData = is_object($record[$groupName]) ? (string)$record[$groupName] : $record[$groupName];

            $extractedData[$xData][$groupData] = $yData;
        }

        return $extractedData;
    }

    protected function validateParameters(array $config)
    {
        if (!isset($config['chart'][static::X_AXIS_ALIAS]) ||
            !isset($config['chart'][static::Y_AXIS_ALIAS]) ||
            !isset($config['chart'][static::Y_ITEMS_GROUP_NAME])
        ) {
            throw new \RuntimeException("Report has missed required parameters");
        }
        return true;
    }
}