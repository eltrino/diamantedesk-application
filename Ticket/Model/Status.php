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
    const NEW_ONE     = 'new';
    const OPEN        = 'open';
    const PENDING     = 'pending';
    const IN_PROGRESS = 'in progress';
    const CLOSED      = 'closed';
    const ON_HOLD     = 'on hold';

    /**
     * @var string
     */
    private $status;

    /**
     * @param $status
     */
    public function __construct($status)
    {
        if (false === in_array($status, self::getPossibleValues())) {
            throw new \InvalidArgumentException('Given status is wrong');
        }

        $this->status = (string) $status;
    }

    /**
     * @return array
     */
    private static function getPossibleValues()
    {
        return [self::NEW_ONE, self::OPEN, self::PENDING, self::IN_PROGRESS, self::CLOSED, self::ON_HOLD];
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