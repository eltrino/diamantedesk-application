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
namespace Eltrino\DiamanteDeskBundle\Tests\Entity;

use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
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
            Status::OPEN
        );

        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('open', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertEquals($assignee, $ticket->getAssignee());
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
            Status::NEW_ONE
        );

        $this->assertEquals('Subject', $ticket->getSubject());
        $this->assertEquals('Description', $ticket->getDescription());
        $this->assertEquals($branch, $ticket->getBranch());
        $this->assertEquals('new', $ticket->getStatus()->getValue());
        $this->assertEquals($reporter, $ticket->getReporter());
        $this->assertEquals($assignee, $ticket->getAssignee());
    }

    public function testUpdate()
    {
        $ticket = $this->createTicket();

        $ticket->update('New Subject', 'New Description', $this->createReporter(), Status::CLOSED);

        $this->assertEquals('New Subject', $ticket->getSubject());
        $this->assertEquals('New Description', $ticket->getDescription());
        $this->assertEquals(Status::CLOSED, $ticket->getStatus()->getValue());
    }

    public function testAssign()
    {
        $newAssignee = new User();

        $ticket = $this->createTicket();

        $ticket->assign($newAssignee);

        $this->assertEquals($newAssignee, $ticket->getAssignee());
    }

    private function createTicket()
    {
        $ticket = new Ticket(
            self::TICKET_SUBJECT,
            self::TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
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
}
