<?php

namespace Diamante\DeskBundle\Report\ChartType;

class GroupedBarChart extends LineChart
{
    const X_ITEM_COUNT_ALIAS = 'items-per-group';

    protected $xItemCountValue = 1;

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

        $this->xItemCountValue = isset($config['chart'][static::X_ITEM_COUNT_ALIAS]) ?
            $config['chart'][static::X_ITEM_COUNT_ALIAS] : $this->xItemCountValue;

        $extractedData = [];
        $groupIndex = 0;

        foreach ($records as $record) {
            $extractedData[$groupIndex] = [
                'x' => $record[$xPropertyName]
            ];
            for ($i = 1; $i <= $this->xItemCountValue; $i++) {
                $extractedData[$groupIndex] = [
                    'y' => [
                        $i => $record[$yPropertyName]
                    ]
                ];
            }
            $groupIndex++;
        }

        return $extractedData;
    }
}