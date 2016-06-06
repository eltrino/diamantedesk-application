<?php

namespace Diamante\DeskBundle\Report\ChartType;

class GroupedLineChart extends AbstractChart
{
    const X_AXIS_ALIAS = 'x-axis';
    const Y_AXIS_ALIAS = 'y-axis';
    const Y_ITEMS_GROUP_NAME = 'group';
    const Y_ITEMS_ALIAS = 'items';

    /**
     * @param array $records
     * @param $config
     * @return array
     */
    public function extractData(array $records, $config)
    {
        $this->validateParameters($config);

        $xPropertyName = $config['chart'][self::X_AXIS_ALIAS];
        $yPropertyName = $config['chart'][self::Y_AXIS_ALIAS];

        $extractedData = [];
        $groupName = '';
        if (isset($config['chart'][self::Y_ITEMS_GROUP_NAME])) {
            $groupName = $config['chart'][self::Y_ITEMS_GROUP_NAME];
        }

        foreach ($records as $record) {
            $xData = is_object($record[$xPropertyName]) ? (string)$record[$xPropertyName] : $record[$xPropertyName];
            if (!is_array($yPropertyName)) {
                $yData = is_object($record[$yPropertyName]) ? (string)$record[$yPropertyName] : $record[$yPropertyName];
            }
            if ($groupName) {
                $groupData = is_object($record[$groupName]) ? (string)$record[$groupName] : $record[$groupName];
            }

            if (is_array($config['chart'][self::Y_AXIS_ALIAS])) {
                if (isset($config['chart'][self::Y_AXIS_ALIAS][self::Y_ITEMS_ALIAS])) {
                    foreach ($config['chart'][self::Y_AXIS_ALIAS][self::Y_ITEMS_ALIAS] as $yItem) {
                        $extractedData[$xData][$yItem] = isset($record[$yItem]) ? $record[$yItem] : false;
                    }
                }
            } else {
                $extractedData[$xData][$groupData] = $yData;
            }

        }

        return $extractedData;
    }

    protected function validateParameters(array $config)
    {
        if (!isset($config['chart'][self::X_AXIS_ALIAS]) || !isset($config['chart'][self::Y_AXIS_ALIAS])) {
            throw new \RuntimeException("Report has missed required parameters");
        }
        return true;
    }
}