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

use Diamante\DeskBundle\Model\Branch\Branch;

class TicketKey
{
    /**
     * @var string
     */
    private $branchKey;

    /**
     * @var int
     */
    private $ticketSequenceNumber;

    public function __construct($branchKey, $ticketSequenceNumber)
    {
        $this->validate($branchKey, $ticketSequenceNumber);
        $this->branchKey = $branchKey;
        $this->ticketSequenceNumber = $ticketSequenceNumber;
    }

    /**
     * @param string $branchKey
     * @param integer $ticketSequenceNumber
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validate($branchKey, $ticketSequenceNumber)
    {
        if (false === is_string($branchKey) || empty($branchKey)) {
            throw new \InvalidArgumentException('Branch key should be a not empty string.');
        }

        if (false === is_int($ticketSequenceNumber) || $ticketSequenceNumber < 1) {
            throw new \InvalidArgumentException('Ticket number should be an integer value grater than 0.');
        }
    }

    /**
     * @return string
     */
    public function getBranchKey()
    {
        return $this->branchKey;
    }

    /**
     * @return int
     */
    public function getTicketSequenceNumber()
    {
        return $this->ticketSequenceNumber;
    }

    /**
     * Returns Ticket Key concatenated from Branch key and Ticket number
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s-%d", $this->branchKey, $this->ticketSequenceNumber);
    }

    public static function create(Branch $branch, Ticket $ticket)
    {
        return new TicketKey($branch->getKey(), $ticket->getSequenceNumber()->getValue());
    }

    public static function from($ticketKey)
    {
        $dividerPosition = strrpos($ticketKey, '-');
        if ($dividerPosition < 1) {
            throw new \LogicException('Ticket key string has wrong format.');
        }
        return new TicketKey(substr($ticketKey, 0, $dividerPosition), (int) substr($ticketKey, $dividerPosition + 1));
    }
} 
