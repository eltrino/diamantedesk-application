<?php

namespace Diamante\DeskBundle\Report\ChartType;

class PieChart extends AbstractChart
{
    const LABEL_ALIAS = 'label';
    const DATA_ALIAS = 'data';

    public function extractData(array $records, $config)
    {
        $this->validateParameters($config);

        $labelField = $config['chart'][static::LABEL_ALIAS];
        $dataField = $config['chart'][static::DATA_ALIAS];


        $extractedData = [];
        foreach ($records as $record) {
            $extractedData[] = [
                static::LABEL_ALIAS => is_object($record[$labelField]) ? (string)$record[$labelField] : $record[$labelField],
                static::DATA_ALIAS  => is_object($record[$dataField]) ? (string)$record[$dataField] : $record[$dataField],
            ];
        }

        return $extractedData;
    }

    protected function validateParameters(array $config)
    {
        if (!isset($config['chart'][static::LABEL_ALIAS]) || !isset($config['chart'][static::DATA_ALIAS])) {
            throw new \RuntimeException("Report has missed required parameters");
        }
        return true;
    }
}