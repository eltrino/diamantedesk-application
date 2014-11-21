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

use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Oro\Bundle\UserBundle\Entity\User;

class TicketTest extends \PHPUnit_Framework_TestCase
{
    const TICKET_SUBJECT      = 'Subject';
    const TICKET_DESCRIPTION  = 'Description';

    public function testCreateWhenStatusIsNotNull()
    {
        $branch = $this->createBranch();
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();
        $ticket = new Ticket(
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            Source::PHONE,
            Priority::PRIORITY_LOW,
            Status::OPEN
        );

        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('open', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertEquals($assignee, $ticket->getAssignee());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
    }

    public function testCreateWhenStatusIsNull()
    {
        $branch = $this->createBranch();
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();
        $ticket = new Ticket(
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            Source::PHONE,
            Priority::PRIORITY_LOW,
            Status::NEW_ONE
        );

        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('new', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertEquals($assignee, $ticket->getAssignee());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
    }

    public function testUpdate()
    {
        $ticket = $this->createTicket();
        $newReporter = new User();

        $ticket->update('New Subject',
            'New Description',
            $newReporter,
            Priority::PRIORITY_LOW,
            Status::CLOSED,
            Source::PHONE
        );

        $this->assertEquals('New Subject', $ticket->getSubject());
        $this->assertEquals('New Description', $ticket->getDescription());
        $this->assertEquals($newReporter, $ticket->getReporter());
        $this->assertEquals(Status::CLOSED, $ticket->getStatus()->getValue());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
    }

    public function testAssign()
    {
        $newAssignee = new User();

        $ticket = $this->createTicket();

        $ticket->assign($newAssignee);

        $this->assertEquals($newAssignee->getId(), $ticket->getAssignee()->getId());
    }

    private function createTicket()
    {
        $ticket = new Ticket(
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Source::PHONE,
            Priority::PRIORITY_LOW,
            Status::OPEN
        );

        return $ticket;
    }

    private function createBranch()
    {
        return new Branch('DUMMY_NAME', 'DUMYY_DESC');
    }

    private function createReporter()
    {
        return new User();
    }

    private function createAssignee()
    {
        return new User();
    }

    /**
     * @test
     */
    public function thatUpdateProperty()
    {
        $ticket = $this->createTicket();
        $subject = 'Updated subject';
        $ticket->updateProperty('subject', $subject);
        $this->assertEquals($subject, $ticket->getSubject());
    }

    /**
     * @test
     *
     * @expectedException \DomainException
     * @expectedExceptionMessage Ticket does not have "invalid_property" property.
     */
    public function thatDoesNotUpdateInvalidProperty()
    {
        $ticket = $this->createTicket();
        $ticket->updateProperty('invalid_property', 'value');
    }
}
