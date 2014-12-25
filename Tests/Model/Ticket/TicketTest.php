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
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\User\User;

class TicketTest extends \PHPUnit_Framework_TestCase
{
    const TICKET_SUBJECT      = 'Subject';
    const TICKET_DESCRIPTION  = 'Description';
    const REPORTER_ID         = 1;

    public function testCreates()
    {
        $branch = $this->createBranch();
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(null),
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::OPEN)
        );

        $this->assertEquals(new UniqueId('unique_id'), $ticket->getUniqueId());
        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('open', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertEquals($assignee, $ticket->getAssignee());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());

        $this->assertNull($ticket->getSequenceNumber()->getValue());
        $this->assertNull($ticket->getKey());
    }

    public function testTicketKeyInitialization()
    {
        $ticketSequenceNumberValue = 12;
        $branch = new Branch('DB', 'DUMMY BRANCH', 'DUMYY_DESC');
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber($ticketSequenceNumberValue),
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::OPEN)
        );

        $this->assertEquals($branch->getKey() . '-' . $ticketSequenceNumberValue, (string) $ticket->getKey());
    }

    public function testCreateWhenStatusIsNull()
    {
        $branch = new Branch('DN', 'DUMMY NAME', 'DUMYY_DESC');
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(null),
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::NEW_ONE)
        );

        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('new', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertEquals($assignee, $ticket->getAssignee());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
    }

    public function testCreateWhenAssigneeIsNull()
    {
        $branch = $this->createBranch();
        $reporter = $this->createReporter();
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(null),
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $branch,
            $reporter,
            null,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::NEW_ONE)
        );

        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('new', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertNull($ticket->getAssignee());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
    }

    public function testUpdate()
    {
        $ticket = $this->createTicket();
        $newReporter = new User(self::REPORTER_ID, User::TYPE_ORO);

        $ticket->update('New Subject',
            'New Description',
            $newReporter,
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED),
            new Source(Source::PHONE)
        );

        $this->assertEquals('New Subject', $ticket->getSubject());
        $this->assertEquals('New Description', $ticket->getDescription());
        $this->assertEquals($newReporter, $ticket->getReporter());
        $this->assertEquals(Status::CLOSED, $ticket->getStatus()->getValue());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
    }

    public function testAssign()
    {
        $newAssignee = new OroUser();

        $ticket = $this->createTicket();

        $ticket->assign($newAssignee);

        $this->assertEquals($newAssignee->getId(), $ticket->getAssignee()->getId());
    }

    public function testMove()
    {
        $newBranch = $this->createBranch();
        $ticket = $this->createTicket();
        $ticket->move($newBranch);
        $this->assertEquals($newBranch->getId(), $ticket->getBranch()->getId());
    }

    private function createTicket()
    {
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(null),
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::OPEN)
        );

        return $ticket;
    }

    private function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMYY_DESC');
    }

    private function createReporter()
    {
        return new User(self::REPORTER_ID, User::TYPE_DIAMANTE);
    }

    private function createAssignee()
    {
        return new OroUser();
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
