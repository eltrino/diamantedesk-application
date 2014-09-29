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
namespace Diamante\DeskBundle\Tests\Api\Internal;

use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Api\Command\AssigneeTicketCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;
use Diamante\DeskBundle\Api\Command\UpdateTicketCommand;
use Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\DBAL\Types\TicketPriorityType;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Api\Internal\TicketServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;

class TicketServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID     = 1;
    const DUMMY_ATTACHMENT_ID = 1;
    const DUMMY_TICKET_SUBJECT      = 'Subject';
    const DUMMY_TICKET_DESCRIPTION  = 'Description';
    const DUMMY_FILENAME      = 'dummy_filename.ext';
    const DUMMY_FILE_CONTENT  = 'DUMMY_CONTENT';
    const DUMMY_STATUS        = 'dummy';

    /**
     * @var TicketServiceImpl
     */
    private $ticketService;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\AttachmentService
     * @Mock \Diamante\DeskBundle\Model\Ticket\AttachmentService
     */
    private $ticketAttachmentService;

    /**
     * @var \Diamante\DeskBundle\Entity\Ticket
     * @Mock \Diamante\DeskBundle\Entity\Ticket
     */
    private $ticket;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $branchRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketFactory
     * @Mock \Diamante\DeskBundle\Model\Ticket\TicketFactory
     */
    private $ticketFactory;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\UserService
     * @Mock \Diamante\DeskBundle\Model\Shared\UserService
     */
    private $userService;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->ticketService = new TicketServiceImpl(
            $this->ticketRepository,
            $this->branchRepository,
            $this->ticketFactory,
            $this->ticketAttachmentService,
            $this->userService,
            $this->securityFacade
        );
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithDefaultStatusAndNoAttachments()
    {
        $branchId = 1;
        $branch = $this->createBranch();
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo($branchId))
            ->will($this->returnValue($branch));

        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $status = Status::NEW_ONE;
        $priority = Priority::DEFAULT_PRIORITY;
        $source = Source::PHONE;

        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            $source,
            $priority,
            $status
        );

        $this->ticketFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_TICKET_SUBJECT), $this->equalTo(self::DUMMY_TICKET_DESCRIPTION),
            $this->equalTo($branch), $this->equalTo($reporter), $this->equalTo($assignee),
            $this->equalTo($priority), $this->equalTo($source)
        )->will($this->returnValue($ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->ticketAttachmentService->expects($this->exactly(0))->method('createAttachmentsForItHolder');
            //->with($this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY), $this->equalTo($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Ticket'))
            ->will($this->returnValue(true));

        $command = new CreateTicketCommand();
        $command->branch = $branchId;
        $command->subject = self::DUMMY_TICKET_SUBJECT;
        $command->description = self::DUMMY_TICKET_DESCRIPTION;
        $command->reporter = $reporterId;
        $command->assignee = $assigneeId;
        $command->priority = $priority;
        $command->source = $source;

        $this->ticketService->createTicket($command);
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithStatusAndNoAttachments()
    {
        $branchId = 1;
        $branch = $this->createBranch();
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo($branchId))
            ->will($this->returnValue($branch));

        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $status = Status::IN_PROGRESS;
        $priority = Priority::DEFAULT_PRIORITY;
        $source = Source::PHONE;

        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            $source,
            $priority,
            $status
        );

        $this->ticketFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_TICKET_SUBJECT), $this->equalTo(self::DUMMY_TICKET_DESCRIPTION),
            $this->equalTo($branch), $this->equalTo($reporter), $this->equalTo($assignee),
            $this->equalTo($priority), $this->equalTo($source)
        )->will($this->returnValue($ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->ticketAttachmentService->expects($this->exactly(0))->method('createAttachmentsForItHolder');

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Ticket'))
            ->will($this->returnValue(true));

        $command = new CreateTicketCommand();
        $command->branch = $branchId;
        $command->subject = self::DUMMY_TICKET_SUBJECT;
        $command->description = self::DUMMY_TICKET_DESCRIPTION;
        $command->reporter = $reporterId;
        $command->assignee = $assigneeId;
        $command->priority = $priority;
        $command->source = $source;
        $command->status = $status;

        $this->ticketService->createTicket($command);
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithDefaultStatusAndAttachments()
    {
        $branchId = 1;
        $branch = $this->createBranch();
        $this->branchRepository->expects($this->once())->method('get')->with($this->equalTo($branchId))
            ->will($this->returnValue($branch));

        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $status = Status::NEW_ONE;
        $priority = Priority::DEFAULT_PRIORITY;
        $source = Source::PHONE;

        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            $source,
            $priority,
            $status
        );

        $this->ticketFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_TICKET_SUBJECT), $this->equalTo(self::DUMMY_TICKET_DESCRIPTION),
            $this->equalTo($branch), $this->equalTo($reporter), $this->equalTo($assignee),
            $this->equalTo($priority), $this->equalTo($source)
        )->will($this->returnValue($ticket));

        $attachmentInputs = $this->attachmentInputs();

        $this->ticketAttachmentService->expects($this->once())->method('createAttachmentsForItHolder')
            ->with($this->equalTo($attachmentInputs), $this->equalTo($ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Ticket'))
            ->will($this->returnValue(true));

        $command = new CreateTicketCommand();
        $command->branch = $branchId;
        $command->subject = self::DUMMY_TICKET_SUBJECT;
        $command->description = self::DUMMY_TICKET_DESCRIPTION;
        $command->reporter = $reporterId;
        $command->assignee = $assigneeId;
        $command->priority = $priority;
        $command->source = $source;
        $command->status = null;
        $command->attachmentsInput = $attachmentInputs;

        $this->ticketService->createTicket($command);
    }

    /**
     * @test
     */
    public function thatTicketUpdatesWithNoAttachments()
    {
        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $newStatus = Status::IN_PROGRESS;
        $branch = $this->createBranch();

        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            Source::PHONE,
            Priority::DEFAULT_PRIORITY,
            Status::NEW_ONE
        );

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $command = new UpdateTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->subject = self::DUMMY_TICKET_SUBJECT;
        $command->description = self::DUMMY_TICKET_DESCRIPTION;
        $command->reporter = $reporterId;
        $command->assignee = $assigneeId;
        $command->priority = Priority::DEFAULT_PRIORITY;
        $command->source = Source::PHONE;
        $command->status = $newStatus;

        $this->ticketService->updateTicket($command);

        $this->assertEquals($ticket->getStatus()->getValue(), $newStatus);
    }

    /**
     * @test
     */
    public function thatTicketUpdatesWithAttachments()
    {
        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $newStatus = Status::IN_PROGRESS;
        $branch = $this->createBranch();

        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            Source::PHONE,
            Priority::DEFAULT_PRIORITY,
            Status::NEW_ONE
        );

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $attachmentInputs = $this->attachmentInputs();

        $this->ticketAttachmentService->expects($this->once())->method('createAttachmentsForItHolder')
            ->with($this->equalTo($attachmentInputs), $this->equalTo($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $command = new UpdateTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->subject = self::DUMMY_TICKET_SUBJECT;
        $command->description = self::DUMMY_TICKET_DESCRIPTION;
        $command->reporter = $reporterId;
        $command->assignee = $assigneeId;
        $command->priority = Priority::DEFAULT_PRIORITY;
        $command->source = Source::PHONE;
        $command->status = $newStatus;
        $command->attachmentsInput = $attachmentInputs;

        $this->ticketService->updateTicket($command);

        $this->assertEquals($ticket->getStatus()->getValue(), $newStatus);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatAttachmentRetrievingThrowsExceptionWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $retrieveTicketAttachmentCommand = new RetrieveTicketAttachmentCommand();
        $retrieveTicketAttachmentCommand->attachmentId = self::DUMMY_ATTACHMENT_ID;
        $retrieveTicketAttachmentCommand->ticketId     = self::DUMMY_TICKET_ID;
        $this->ticketService->getTicketAttachment($retrieveTicketAttachmentCommand);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Ticket has no such attachment.
     */
    public function thatAttachmentRetrievingThrowsExceptionWhenTicketHasNoAttachment()
    {
        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Source::PHONE,
            Priority::DEFAULT_PRIORITY,
            Status::CLOSED
        );
        $ticket->addAttachment($this->attachment());
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('VIEW'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $retrieveTicketAttachmentCommand = new RetrieveTicketAttachmentCommand();
        $retrieveTicketAttachmentCommand->attachmentId = self::DUMMY_ATTACHMENT_ID;
        $retrieveTicketAttachmentCommand->ticketId     = self::DUMMY_TICKET_ID;
        $this->ticketService->getTicketAttachment($retrieveTicketAttachmentCommand);
    }

    /**
     * @test
     */
    public function thatTicketAttachmentRetrieves()
    {
        $attachment = $this->attachment();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('VIEW'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticket->expects($this->once())->method('getAttachment')->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));

        $retrieveTicketAttachmentCommand = new RetrieveTicketAttachmentCommand();
        $retrieveTicketAttachmentCommand->attachmentId = self::DUMMY_ATTACHMENT_ID;
        $retrieveTicketAttachmentCommand->ticketId     = self::DUMMY_TICKET_ID;
        $this->ticketService->getTicketAttachment($retrieveTicketAttachmentCommand);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatAttachmentsAddingThrowsExceptionWhenTicketNotExists()
    {
        $addTicketAttachmentCommand = new AddTicketAttachmentCommand();
        $addTicketAttachmentCommand->attachments = $this->attachmentInputs();
        $addTicketAttachmentCommand->ticketId    = self::DUMMY_TICKET_ID;
        $this->ticketService->addAttachmentsForTicket($addTicketAttachmentCommand);
    }

    /**
     * @test
     */
    public function thatAttachmentsAddsForTicket()
    {
        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Source::PHONE,
            Priority::DEFAULT_PRIORITY,
            Status::CLOSED
        );
        $attachmentInputs = $this->attachmentInputs();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));
        $this->ticketAttachmentService->expects($this->once())->method('createAttachmentsForItHolder')
            ->with($this->equalTo($attachmentInputs), $this->equalTo($ticket));
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $addTicketAttachmentCommand = new AddTicketAttachmentCommand();
        $addTicketAttachmentCommand->attachments = $attachmentInputs;
        $addTicketAttachmentCommand->ticketId    = self::DUMMY_TICKET_ID;
        $this->ticketService->addAttachmentsForTicket($addTicketAttachmentCommand);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $removeTicketAttachmentCommand = new RemoveTicketAttachmentCommand();
        $removeTicketAttachmentCommand->ticketId = self::DUMMY_TICKET_ID;
        $removeTicketAttachmentCommand->attachmentId = self::DUMMY_ATTACHMENT_ID;
        $this->ticketService->removeAttachmentFromTicket($removeTicketAttachmentCommand);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Ticket has no such attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenTicketHasNoAttachment()
    {
        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Source::PHONE,
            Priority::DEFAULT_PRIORITY,
            Status::CLOSED
        );
        $ticket->addAttachment($this->attachment());
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $removeTicketAttachmentCommand = new RemoveTicketAttachmentCommand();
        $removeTicketAttachmentCommand->ticketId = self::DUMMY_TICKET_ID;
        $removeTicketAttachmentCommand->attachmentId = self::DUMMY_ATTACHMENT_ID;
        $this->ticketService->removeAttachmentFromTicket($removeTicketAttachmentCommand);
    }

    /**
     * @test
     */
    public function thatAttachmentRemovesFromTicket()
    {
        $attachment = $this->attachment();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('getAttachment')->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));

        $this->ticket->expects($this->once())->method('removeAttachment')->with($this->equalTo($attachment));

        $this->ticketAttachmentService->expects($this->once())->method('removeAttachmentFromItHolder')
            ->with($this->equalTo($attachment));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $removeTicketAttachmentCommand = new RemoveTicketAttachmentCommand();
        $removeTicketAttachmentCommand->ticketId = self::DUMMY_TICKET_ID;
        $removeTicketAttachmentCommand->attachmentId = self::DUMMY_ATTACHMENT_ID;
        $this->ticketService->removeAttachmentFromTicket($removeTicketAttachmentCommand);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testUpdateStatusWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $command = new UpdateStatusCommand();
        $command->ticketId = self::DUMMY_TICKET_ID;
        $command->status = self::DUMMY_STATUS;

        $this->ticketService->updateStatus($command);
    }

    /**
     * @test
     */
    public function testUpdateStatus()
    {
        $status = STATUS::NEW_ONE;
        $currentUserId = 3;
        $assignee = $this->createAssignee();
        $assignee->setId($currentUserId);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('updateStatus')->with($status);
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->ticket->expects($this->any())->method('getAssignee')->will($this->returnValue($assignee));

        $this->securityFacade
            ->expects($this->any())
            ->method('getLoggedUserId')
            ->will($this->returnValue($currentUserId));

        $this->securityFacade
            ->expects($this->never())
            ->method('isGranted');

        $command = new UpdateStatusCommand();
        $command->ticketId = self::DUMMY_TICKET_ID;
        $command->status = $status;

        $this->ticketService->updateStatus($command);
    }

    public function testUpdateStatusOfTicketAssignedToSomeoneElse()
    {
        $status = STATUS::NEW_ONE;
        $assignee = $this->createAssignee();
        $assignee->setId(3);
        $currentUserId = 2;

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('updateStatus')->with($status);
        $this->ticket->expects($this->exactly(2))->method('getAssignee')->will($this->returnValue($assignee));
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->securityFacade
            ->expects($this->any())
            ->method('getLoggedUserId')
            ->will($this->returnValue($currentUserId));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $command = new UpdateStatusCommand();
        $command->ticketId = self::DUMMY_TICKET_ID;
        $command->status = $status;

        $this->ticketService->updateStatus($command);
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
     * @return Attachment
     */
    private function attachment()
    {
        return new Attachment(new File('filename.ext'));
    }

    /**
     * @return AttachmentInput
     */
    private function attachmentInputs()
    {
        $attachmentInput = new AttachmentInput();
        $attachmentInput->setFilename(self::DUMMY_FILENAME);
        $attachmentInput->setContent(self::DUMMY_FILE_CONTENT);
        return array($attachmentInput);
    }

    public function testAssignTicket()
    {
        $assigneeId = $currentUserId = 3;
        $assignee = $this->createAssignee();
        $assignee->setId($assigneeId);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $this->ticket->expects($this->any())->method('getAssignee')->will($this->returnValue($assignee));
        $this->ticket->expects($this->once())->method('assign')->with($assignee);
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->securityFacade
            ->expects($this->any())
            ->method('getLoggedUserId')
            ->will($this->returnValue($currentUserId));

        $this->securityFacade
            ->expects($this->never())
            ->method('isGranted');

        $command = new AssigneeTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->assignee = $assigneeId;

        $this->ticketService->assignTicket($command);
    }

    public function testAssignTicketOfTicketAssignedToSomeoneElse()
    {
        $currentUserId = 2;
        $assigneeId = 3;
        $assignee = $this->createAssignee();

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $this->ticket->expects($this->any())->method('getAssigneeId')->will($this->returnValue($assigneeId));
        $this->ticket->expects($this->once())->method('assign')->with($assignee);
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->securityFacade
            ->expects($this->any())
            ->method('getLoggedUserId')
            ->will($this->returnValue($currentUserId));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $command = new AssigneeTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->assignee = $assigneeId;

        $this->ticketService->assignTicket($command);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testAssignTicketWhenTicketDoesNotExist()
    {
        $assigneeId = 3;

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $command = new AssigneeTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->assignee = $assigneeId;

        $this->ticketService->assignTicket($command);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Assignee loading failed, assignee not found.
     */
    public function testAssignTicketWhenAssigneeDoesNotExist()
    {
        $currentUserId = 2;
        $assigneeId = 3;

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->securityFacade
            ->expects($this->any())
            ->method('getLoggedUserId')
            ->will($this->returnValue($currentUserId));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->userService->expects($this->at(0))->method('getUserById')->with($this->equalTo($assigneeId))
            ->will($this->returnValue(null));

        $command = new AssigneeTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->assignee = $assigneeId;

        $this->ticketService->assignTicket($command);
    }

    public function testDeleteTicket()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticketRepository->expects($this->once())->method('remove')->with($this->equalTo($this->ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticketService->deleteTicket(self::DUMMY_TICKET_ID);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testDeleteTicketWhenTicketDoesNotExist()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $this->ticketService->deleteTicket(self::DUMMY_TICKET_ID);
    }
}
