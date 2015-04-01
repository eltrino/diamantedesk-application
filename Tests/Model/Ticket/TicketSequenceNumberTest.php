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

use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;

class TicketSequenceNumberTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCreates()
    {
        $number = new TicketSequenceNumber(12);
        $this->assertEquals(12, $number->getValue());
        $this->assertEquals('12', (string) $number);
    }

    /**
     * @param $value
     * @dataProvider dataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Number can be an integer and greater than 0 or null.
     */
    public function testValidationWhenCreates($value)
    {
        new TicketSequenceNumber($value);
    }

    public function dataProvider()
    {
        return array(
            array(0),
            array(''),
            array(array()),
            array('2')
        );
    }
} 
