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

class Status
{
    const NEW_ONE     = 'new';
    const OPEN        = 'open';
    const PENDING     = 'pending';
    const IN_PROGRESS = 'in_progress';
    const CLOSED      = 'closed';
    const ON_HOLD     = 'on_hold';

    const LABEL_NEW_ONE     = 'New';
    const LABEL_OPEN        = 'Open';
    const LABEL_PENDING     = 'Pending';
    const LABEL_IN_PROGRESS = 'In progress';
    const LABEL_CLOSED      = 'Closed';
    const LABEL_ON_HOLD     = 'On hold';

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    protected static $valueToLabelMap = array();

    /**
     * @param $status
     */
    public function __construct($status)
    {
        static::initValueLabelsMap();

        if (false === isset(static::$valueToLabelMap[$status])) {
            throw new \InvalidArgumentException('Given status is wrong');
        }

        $this->status = (string) $status;
    }

    /**
     * Initialize static array of value to label priorities map
     */
    protected static function initValueLabelsMap()
    {
        if (empty(static::$valueToLabelMap)) {
            static::$valueToLabelMap = [
                self::NEW_ONE     => self::LABEL_NEW_ONE,
                self::OPEN        => self::LABEL_OPEN,
                self::PENDING     => self::LABEL_PENDING,
                self::IN_PROGRESS => self::LABEL_IN_PROGRESS,
                self::CLOSED      => self::LABEL_CLOSED,
                self::ON_HOLD     => self::LABEL_ON_HOLD
            ];
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->status;
    }

    /**
     * Retrieve label of priority
     * @return string
     */
    public function getLabel()
    {
        return static::$valueToLabelMap[$this->status];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

    /**
     * @param Status $status
     * @return bool
     */
    public function equals(Status $status)
    {
        if ($this == $status) {
            return true;
        }

        return $this->status == $status->getValue();
    }

    /**
     * @param Status $status
     * @return bool
     */
    public function notEquals(Status $status)
    {
        return !$this->equals($status);
    }
}
