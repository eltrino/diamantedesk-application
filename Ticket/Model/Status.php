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
namespace Eltrino\DiamanteDeskBundle\Ticket\Model;

class Status
{
    const STATUS_NEW         = 'new';
    const STATUS_OPEN        = 'open';
    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_CLOSED      = 'closed';
    const STATUS_ON_HOLD     = 'on hold';

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private static $possibleValues = array();

    /**
     * @param null $status
     */
    public function __construct($status = null)
    {
        if (false === in_array($status, self::getPossibleValues())) {
            throw new \InvalidArgumentException('Given status is wrong');
        }

        $this->status = (string) $status;
    }

    /**
     * @return array
     */
    public static function getPossibleValues()
    {
        if (empty(self::$possibleValues)) {
            $reflection = new \ReflectionClass(__CLASS__);
            self::$possibleValues = $reflection->getConstants();
        }

        return self::$possibleValues;
    }

    /**
     * @return array
     */
    public static function getOptions()
    {
        return array_combine(
            self::getPossibleValues(), self::getPossibleValues()
        );
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}