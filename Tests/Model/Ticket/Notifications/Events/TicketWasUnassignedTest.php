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
namespace Diamante\DeskBundle\Tests\Model\Ticket\Notifications\Events;

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUnassigned;

class TicketWasUnassignedTest extends \PHPUnit_Framework_TestCase
{
    const ID = 'id';
    const TICKET_SUBJECT = 'Subject';

    public function testGetEventName()
    {
        $ticketWasUpdated = new TicketWasUnassigned(
            self::ID,
            self::TICKET_SUBJECT,
            array()
        );

        $this->assertEquals('ticketWasUnassigned', $ticketWasUpdated->getEventName());
    }
} 