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
namespace Diamante\DeskBundle\Tests\Model\Ticket;

use Diamante\DeskBundle\Model\Ticket\TicketKey;

class TicketKeyTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCreates()
    {
        $key = new TicketKey('DD', 12);
        $this->assertEquals('DD', $key->getBranchKey());
        $this->assertEquals(12, $key->getTicketSequenceNumber());
    }

    /**
     * @param $branchKey
     * @param $ticketSequenceNumber
     * @dataProvider branchKeyProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Branch key should be a not empty string.
     */
    public function testBranchKeyValidationDuringCreation($branchKey, $ticketSequenceNumber)
    {
        new TicketKey($branchKey, $ticketSequenceNumber);
    }

    public function branchKeyProvider()
    {
        return array(
            array(12, 12),
            array('', 12),
            array(array(), 12)
        );
    }

    /**
     * @param $branchKey
     * @param $ticketSequenceNumber
     * @dataProvider ticketSequenceNumberProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Ticket number should be an integer value grater than 0.
     */
    public function testTicketSequenceNumberValidationDuringCreation($branchKey, $ticketSequenceNumber)
    {
        new TicketKey($branchKey, $ticketSequenceNumber);
    }

    public function ticketSequenceNumberProvider()
    {
        return array(
            array('DD', 0),
            array('DD', array()),
            array('DD', 2.5)
        );
    }

    public function testTheFormatsCorrect()
    {
        $key = new TicketKey('DD', 12);
        $this->assertEquals('DD-12', (string) $key);
    }
}
