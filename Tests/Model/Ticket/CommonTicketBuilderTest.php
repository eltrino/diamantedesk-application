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
use Diamante\DeskBundle\Model\Ticket\CommonTicketBuilder;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketFactory;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class CommonTicketBuilderTest extends \PHPUnit_Framework_TestCase
{
    const SUBJECT = 'subject';
    const DESCRIPTION = 'Description';
    const BRANCH_ID = 1;
    const REPORTER_ID = 2;
    const ASSIGNEE_ID = 3;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $branchRepository;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock \Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var CommonTicketBuilder
     */
    private $builder;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $factory = new TicketFactory('\Diamante\DeskBundle\Model\Ticket\Ticket');
        $this->builder = new CommonTicketBuilder($factory, $this->branchRepository, $this->userService);

        $this->branchRepository->expects($this->once())->method('get')->with(self::BRANCH_ID)
            ->will($this->returnValue($this->createBranch()));
        $this->userService->expects($this->once())->method('getByUser')->with(new User(self::ASSIGNEE_ID, User::TYPE_ORO))
            ->will($this->returnValue($this->createAssignee()));
    }

    public function testBuild()
    {
        $this->builder
            ->setSubject(self::SUBJECT)
            ->setDescription(self::DESCRIPTION)
            ->setBranchId(self::BRANCH_ID)
            ->setReporter($this->createReporter())
            ->setAssigneeId(self::ASSIGNEE_ID)
            ->setPriority(Priority::PRIORITY_LOW)
            ->setSource(Source::EMAIL)
            ->setStatus(Status::IN_PROGRESS);

        $ticket = $this->builder->build();

        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Ticket', $ticket);
        $this->assertEquals(self::SUBJECT, $ticket->getSubject());
        $this->assertEquals(self::DESCRIPTION, $ticket->getDescription());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Branch\Branch', $ticket->getBranch());
        $this->assertInstanceOf('\Diamante\UserBundle\Model\User', $ticket->getReporter());
        $this->assertInstanceOf('\Oro\Bundle\UserBundle\Entity\User', $ticket->getAssignee());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Priority', $ticket->getPriority());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Status', $ticket->getStatus());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Source', $ticket->getSource());
        $this->assertEquals(Priority::PRIORITY_LOW, $ticket->getPriority()->getValue());
        $this->assertEquals(Status::IN_PROGRESS, $ticket->getStatus()->getValue());
        $this->assertEquals(Source::EMAIL, $ticket->getSource()->getValue());
    }

    public function testBuildWhenDefaultValuesApplies()
    {

        $reporter = $this->createReporter();

        $this->builder
            ->setSubject(self::SUBJECT)
            ->setDescription(self::DESCRIPTION)
            ->setBranchId(self::BRANCH_ID)
            ->setReporter((string)$reporter)
            ->setAssigneeId(self::ASSIGNEE_ID);

        $ticket = $this->builder->build();

        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Ticket', $ticket);
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Priority', $ticket->getPriority());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Status', $ticket->getStatus());
        $this->assertInstanceOf('\Diamante\DeskBundle\Model\Ticket\Source', $ticket->getSource());
        $this->assertEquals(Priority::PRIORITY_MEDIUM, $ticket->getPriority()->getValue());
        $this->assertEquals(Status::NEW_ONE, $ticket->getStatus()->getValue());
        $this->assertEquals(Source::PHONE, $ticket->getSource()->getValue());
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
} 
