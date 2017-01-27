<?php

namespace Diamante\DeskBundle\Twig\Extensions;

use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;

class CommentExtension extends \Twig_Extension
{
    /**
     * @var DateTimeFormatter
     */
    protected $formatter;

    /**
     * @param DateTimeFormatter $formatter
     */
    public function __construct(DateTimeFormatter $formatter)
    {
        $this->formatter = $formatter;
    }
    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'time_diff',
                [$this, 'getTimeDiff'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'bites_to_human',
                [$this, 'getBitesToHuman'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Get date diff twitter style
     *
     * @param \DateTime $date
     * @return string
     */
    public function getTimeDiff($date)
    {
        $time = time() - $date->getTimestamp();

        $units = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($units as $unit => $val) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return ($val == 'second')? 'a few seconds ago' :
                 (($numberOfUnits>1) ? $numberOfUnits : 'a')
                 .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
        }
    }

    /**
     * Get filesize in human friendly format
     *
     * @param int $bytes
     * @return string
     */

    public function getBitesToHuman($bytes){
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        switch ($bytes){
            case $bytes < $kilobyte:
                return $bytes . ' B';
            case $bytes < $megabyte:
                return number_format(($bytes / $kilobyte), 2, '.', ',') . ' KB';
            case $bytes < $gigabyte:
                return number_format(($bytes / $megabyte), 2, '.', ',') . ' MB';
            case $bytes < $terabyte:
                return number_format(($bytes / $gigabyte), 2, '.', ',') . ' GB';
            default:
                return number_format(($bytes / $terabyte), 2, '.', ',') . ' TB';
        }

    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'diamante_time_diff_extension';
    }
}
