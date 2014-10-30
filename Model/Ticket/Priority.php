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
namespace Diamante\DeskBundle\Model\Ticket;

class Priority
{
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    const PRIORITY_LOW_LABEL = 'Low';
    const PRIORITY_MEDIUM_LABEL = 'Medium';
    const PRIORITY_HIGH_LABEL = 'High';

    private $priority;

    private static $valueToLabelMap = array();

    public function __construct($priority = null)
    {
        if (is_null($priority)) {
            $priority = self::PRIORITY_MEDIUM;
        }

        static::initValueLabelsMap();

        if (false === isset(static::$valueToLabelMap[$priority])) {
            throw new \InvalidArgumentException("Priority doesn't exist.");
        }

        $this->priority = $priority;
    }

    /**
     * Initialize static array of value to label priorities map
     */
    private static function initValueLabelsMap()
    {
        if (empty(static::$valueToLabelMap)) {
            static::$valueToLabelMap = [
                self::PRIORITY_LOW => self::PRIORITY_LOW_LABEL,
                self::PRIORITY_MEDIUM => self::PRIORITY_MEDIUM_LABEL,
                self::PRIORITY_HIGH => self::PRIORITY_HIGH_LABEL
            ];
        }
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->priority;
    }

    /**
     * Retrieve label of priority
     * @return string
     */
    public function getLabel()
    {
        return static::$valueToLabelMap[$this->priority];
    }

    public function __toString()
    {
        return $this->getLabel();
    }
}
