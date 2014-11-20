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

class TicketSequenceNumber
{
    /**
     * @var int
     */
    private $number;

    public function __construct($number = null)
    {
        if (false == is_null($number) && (false == is_int($number) || $number < 1)) {
            throw new \InvalidArgumentException('Number can be an integer and greater than 0 or null.');
        }
        $this->number = $number;
    }

    /**
     * @return int|null
     */
    public function getValue()
    {
        return $this->number;
    }

    public function __toString()
    {
        return (string) $this->number;
    }
}
