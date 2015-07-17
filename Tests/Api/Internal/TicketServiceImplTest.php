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

use Diamante\DeskBundle\Api\Command\UpdatePropertiesCommand;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Api\Command\AssigneeTicketCommand;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;
use Diamante\DeskBundle\Api\Command\UpdateTicketCommand;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Api\Internal\TicketServiceImpl;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

use Diamante\DeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveTicketAttachmentCommand;
use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;

class TicketServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID     = 1;
    const DUMMY_TICKET_KEY    = 'DT-1';
    const DUMMY_ATTACHMENT_ID = 1;
    const SUBJECT      = 'Subject';
    const DESCRIPTION  = 'Description';
    const DUMMY_FILENAME      = 'dummy_filename.ext';
    const DUMMY_FILE_CONTENT  = 'DUMMY_CONTENT';
    const DUMMY_STATUS        = 'dummy';

    /**
     * @var TicketServiceImpl
     */
    private $ticketService;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketRepository
     * @Mock \Diamante\DeskBundle\Model\Ticket\TicketRepository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Manager
     * @Mock \Diamante\DeskBundle\Model\Attachment\Manager
     */
    private $attachmentManager;

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
     * @var \Diamante\DeskBundle\Model\Ticket\TicketBuilder
     * @Mock \Diamante\DeskBundle\Model\Ticket\TicketBuilder
     */
    private $ticketBuilder;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock \Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService
     * @Mock \Diamante\DeskBundle\Model\Shared\Authorization\AuthorizationService
     */
    private $authorizationService;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     * @Mock \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $dispatcher;

    /**
     * @var NotificationDeliveryManager
     */
    private $notificationDeliveryManager;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Notifier
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Notifier
     */
    private $notifier;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $ticketHistoryRepository;

    /**
     * @var \Oro\Bundle\TagBundle\Entity\TagManager
     * @Mock \Oro\Bundle\TagBundle\Entity\TagManager
     */
    private $tagManager;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->notificationDeliveryManager = new NotificationDeliveryManager();

        $this->ticketService = new TicketServiceImpl(
            $this->em,
            $this->ticketRepository,
            $this->branchRepository,
            $this->ticketBuilder,
            $this->attachmentManager,
            $this->userService,
            $this->authorizationService,
            $this->dispatcher,
            $this->notificationDeliveryManager,
            $this->notifier,
            $this->ticketHistoryRepository,
            $this->tagManager,
            $this->securityFacade
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testLoadTicketByKeyThrowsExceptionIfTicketDoesNotExist()
    {
        $key = 'TK-1';

        $this->ticketRepository->expects($this->once())->method('getByTicketKey')
            ->with(new TicketKey('TK', 1))
            ->will($this->returnValue(null));

        $this->ticketService->loadTicketByKey($key);
    }

    public function testLoadTicketByKey()
    {
        $key = 'TK-1';
        $ticket = $this->ticket;

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with('VIEW', $ticket)
            ->will($this->returnValue(true));

        $this->ticketRepository->expects($this->once())->method('getByTicketKey')
            ->with(new TicketKey('TK', 1))
            ->will($this->returnValue($ticket));

        $this->ticketService->loadTicketByKey($key);
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithDefaultStatusAndNoAttachments()
    {
        $branchId = 1;
        $reporterId = 2;
        $assigneeId = 3;

        $status = Status::NEW_ONE;
        $priority = Priority::PRIORITY_LOW;
        $source = Source::PHONE;
        $number = new TicketSequenceNumber(null);
        $reporter = $this->createReporter($reporterId);

        $ticket = new Ticket(
            UniqueId::generate(),
            $number,
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $reporter,
            $this->createAssignee(),
            new Source($source),
            new Priority($priority),
            new Status($status)
        );

        $this->ticketBuilder->expects($this->once())->method('setSubject')->with(self::SUBJECT)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setDescription')->with(self::DESCRIPTION)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setBranchId')->with($branchId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setReporter')->with((string)$reporter)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setAssigneeId')->with($assigneeId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setSource')->with($source)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setPriority')->with($priority)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setStatus')->with(null)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('build')->will($this->returnValue($ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Ticket'))
            ->will($this->returnValue(true));

        $command = new CreateTicketCommand();
        $command->branch = $branchId;
        $command->subject = self::SUBJECT;
        $command->description = self::DESCRIPTION;
        $command->reporter = (string)$reporter;
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
        $reporterId = 2;
        $assigneeId = 3;

        $status = Status::IN_PROGRESS;
        $priority = Priority::PRIORITY_LOW;
        $source = Source::PHONE;
        $number = new TicketSequenceNumber(null);
        $reporter = $this->createReporter($reporterId);

        $ticket = new Ticket(
            UniqueId::generate(),
            $number,
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $reporter,
            $this->createAssignee(),
            new Source($source),
            new Priority($priority),
            new Status($status)
        );

        $this->ticketBuilder->expects($this->once())->method('setSubject')->with(self::SUBJECT)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setDescription')->with(self::DESCRIPTION)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setBranchId')->with($branchId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setReporter')->with((string)$reporter)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setAssigneeId')->with($assigneeId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setSource')->with($source)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setPriority')->with($priority)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setStatus')->with($status)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('build')->will($this->returnValue($ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Ticket'))
            ->will($this->returnValue(true));

        $command = new CreateTicketCommand();
        $command->branch = $branchId;
        $command->subject = self::SUBJECT;
        $command->description = self::DESCRIPTION;
        $command->reporter = (string)$reporter;
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
        $reporterId = 2;
        $assigneeId = 3;

        $status = Status::NEW_ONE;
        $priority = Priority::PRIORITY_LOW;
        $source = Source::PHONE;
        $number = new TicketSequenceNumber(null);
        $reporter = $this->createReporter($reporterId);

        $ticket = new Ticket(
            UniqueId::generate(),
            $number,
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $reporter,
            $this->createAssignee(),
            new Source($source),
            new Priority($priority),
            new Status($status)
        );

        $this->ticketBuilder->expects($this->once())->method('setSubject')->with(self::SUBJECT)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setDescription')->with(self::DESCRIPTION)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setBranchId')->with($branchId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setReporter')->with((string)$reporter)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setAssigneeId')->with($assigneeId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setSource')->with($source)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setPriority')->with($priority)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setStatus')->with(null)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('build')->will($this->returnValue($ticket));

        $attachmentInputs = $this->attachmentInputs();

        $this->attachmentManager->expects($this->exactly(count($attachmentInputs)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($ticket)
            );

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Ticket'))
            ->will($this->returnValue(true));

        $command = new CreateTicketCommand();
        $command->branch = $branchId;
        $command->subject = self::SUBJECT;
        $command->description = self::DESCRIPTION;
        $command->reporter = (string)$reporter;
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
        $reporter = $this->createReporter($reporterId);
        $assignee = $this->createAssignee();

        $this->userService->expects($this->atLeastOnce())->method('getByUser')->with(new User($assigneeId, User::TYPE_ORO))
            ->will($this->returnValue($assignee));

        $newStatus = Status::IN_PROGRESS;
        $branch = $this->createBranch();

        $ticket = new Ticket(
            UniqueId::generate(),
            new TicketSequenceNumber(12),
            self::SUBJECT,
            self::DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::NEW_ONE)
        );

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $command = new UpdateTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->subject = self::SUBJECT;
        $command->description = self::DESCRIPTION;
        $command->reporter = (string)$reporter;
        $command->assignee = $assigneeId;
        $command->priority = Priority::PRIORITY_LOW;
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
        $reporter = $this->createReporter($reporterId);
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))->method('getByUser')->with(new User($assigneeId, User::TYPE_ORO))
            ->will($this->returnValue($assignee));

        $newStatus = Status::IN_PROGRESS;
        $branch = $this->createBranch();

        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(12),
            self::SUBJECT,
            self::DESCRIPTION,
            $branch,
            $reporter,
            $assignee,
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::NEW_ONE)
        );

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $attachmentInputs = $this->attachmentInputs();

        $this->attachmentManager->expects($this->exactly(count($attachmentInputs)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($ticket)
            );

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $command = new UpdateTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->subject = self::SUBJECT;
        $command->description = self::DESCRIPTION;
        $command->reporter = (string)$reporter;
        $command->assignee = $assigneeId;
        $command->priority = Priority::PRIORITY_LOW;
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
        $reporterId = 1;
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(12),
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $this->createReporter($reporterId),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED)
        );
        $ticket->addAttachment($this->attachment());
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
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
    public function thatListsTicketAttachments()
    {
        $this->ticketRepository->expects($this->once())->method('get')
            ->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->authorizationService
            ->expects($this->exactly(2))
            ->method('isActionPermitted')
            ->with($this->equalTo('VIEW'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $attachment = $this->attachment();

        $this->ticket->expects($this->once())->method('getAttachments')->will($this->returnValue(
            array($attachment)
        ));

        $attachments = $this->ticketService->listTicketAttachments(self::DUMMY_TICKET_ID);

        $this->assertNotNull($attachments);
        $this->assertContains($attachment, $attachments);
    }

    /**
     * @test
     */
    public function thatTicketAttachmentRetrieves()
    {
        $attachment = $this->attachment();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
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
        $addTicketAttachmentCommand->attachmentsInput = $this->attachmentInputs();
        $addTicketAttachmentCommand->ticketId    = self::DUMMY_TICKET_ID;
        $this->ticketService->addAttachmentsForTicket($addTicketAttachmentCommand);
    }

    /**
     * @test
     */
    public function thatAttachmentsAddsForTicket()
    {
        $ticket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(12),
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED)
        );
        $attachmentInputs = $this->attachmentInputs();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));
        $this->attachmentManager->expects($this->exactly(count($attachmentInputs)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($ticket)
            );
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($ticket))
            ->will($this->returnValue(true));

        $addTicketAttachmentCommand = new AddTicketAttachmentCommand();
        $addTicketAttachmentCommand->attachmentsInput = $attachmentInputs;
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
            new UniqueId('unique_id'),
            new TicketSequenceNumber(12),
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED)
        );
        $ticket->addAttachment($this->attachment());
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
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
        $attachment = new Attachment(new File('some/path/file.ext'));
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('getAttachment')->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));

        $this->ticket->expects($this->once())->method('removeAttachment')->with($this->equalTo($attachment));

        $this->attachmentManager->expects($this->once())->method('deleteAttachment')->with($this->equalTo($attachment));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

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
        $currentUserId = $assigneeId = 2;
        $currentUser = $this->createOroUser();
        $currentUser->setId($currentUserId);

        $assignee = $this->createAssignee();
        $assignee->setId($assigneeId);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('updateStatus')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\Status'),
                    $this->attributeEqualTo('status', $status)
                )
            );
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->ticket->expects($this->any())->method('getAssignee')->will($this->returnValue($assignee));

        $this->authorizationService
            ->expects($this->any())
            ->method('getLoggedUser')
            ->will($this->returnValue($currentUser));

        $this->authorizationService
            ->expects($this->never())
            ->method('isActionPermitted');

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

        $command = new UpdateStatusCommand();
        $command->ticketId = self::DUMMY_TICKET_ID;
        $command->status = $status;

        $this->ticketService->updateStatus($command);
    }

    public function testUpdateStatusOfTicketAssignedToSomeoneElse()
    {
        $status = STATUS::NEW_ONE;
        $assigneeId = 3;
        $currentUserId = 2;
        $assignee = $this->createAssignee();
        $assignee->setId($assigneeId);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('updateStatus')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\Status'),
                    $this->attributeEqualTo('status', $status)
                )
            );
        $this->ticket->expects($this->exactly(2))->method('getAssignee')->will($this->returnValue($assignee));
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $currentUser = $this->createOroUser();
        $currentUser->setId($currentUserId);
        $this->authorizationService
            ->expects($this->any())
            ->method('getLoggedUser')
            ->will($this->returnValue($currentUser));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

        $command = new UpdateStatusCommand();
        $command->ticketId = self::DUMMY_TICKET_ID;
        $command->status = $status;

        $this->ticketService->updateStatus($command);
    }

    private function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMYY_DESC');
    }

    private function createReporter($id = 1)
    {
        return new User($id, User::TYPE_DIAMANTE);
    }

    private function createAssignee()
    {
        return $this->createOroUser();
    }

    private function createOroUser()
    {
        return new OroUser();
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

        $currentUser = $this->createOroUser();
        $currentUser->setId($currentUserId);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->userService->expects($this->at(0))->method('getByUser')->with($this->equalTo(new User($assigneeId, User::TYPE_ORO)))
            ->will($this->returnValue($assignee));

        $this->ticket->expects($this->any())->method('getAssignee')->will($this->returnValue($assignee));
        $this->ticket->expects($this->once())->method('assign')->with($assignee);
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->authorizationService
            ->expects($this->any())
            ->method('getLoggedUser')
            ->will($this->returnValue($currentUser));

        $this->authorizationService
            ->expects($this->never())
            ->method('isActionPermitted');

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

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
        $currentUser = $this->createOroUser();
        $currentUser->setId($currentUserId);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->userService->expects($this->at(0))->method('getByUser')->with($this->equalTo(new User($assigneeId, User::TYPE_ORO)))
            ->will($this->returnValue($assignee));

        $this->ticket->expects($this->any())->method('getAssigneeId')->will($this->returnValue($assigneeId));
        $this->ticket->expects($this->once())->method('assign')->with($assignee);
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->authorizationService
            ->expects($this->any())
            ->method('getLoggedUser')
            ->will($this->returnValue($currentUserId));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

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

        $this->authorizationService
            ->expects($this->any())
            ->method('getLoggedUserId')
            ->will($this->returnValue($currentUserId));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->userService->expects($this->at(0))->method('getByUser')->with($this->equalTo(new User($assigneeId, User::TYPE_ORO)))
            ->will($this->returnValue(null));

        $command = new AssigneeTicketCommand();
        $command->id = self::DUMMY_TICKET_ID;
        $command->assignee = $assigneeId;

        $this->ticketService->assignTicket($command);
    }

    public function testDeleteTicketById()
    {
        $this->ticket->expects($this->any())->method('getAttachments')
            ->will($this->returnValue(array($this->attachment())));

        $this->ticketRepository->expects($this->once())->method('get')
            ->with(self::DUMMY_TICKET_ID)
            ->will($this->returnValue($this->ticket));

        $this->ticketRepository->expects($this->once())->method('remove')->with($this->equalTo($this->ticket));

        $this->attachmentManager->expects($this->exactly(count($this->ticket->getAttachments())))
            ->method('deleteAttachment')
            ->with($this->isInstanceOf('\Diamante\DeskBundle\Model\Attachment\Attachment'));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('DELETE'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

        $this->ticketService->deleteTicket(self::DUMMY_TICKET_ID);
    }

    public function testDeleteTicketByKey()
    {
        $this->ticket->expects($this->any())->method('getAttachments')
            ->will($this->returnValue(array($this->attachment())));

        $this->ticketRepository->expects($this->once())->method('getByTicketKey')
            ->with(new TicketKey('DT', 1))
            ->will($this->returnValue($this->ticket));

        $this->ticketRepository->expects($this->once())->method('remove')->with($this->equalTo($this->ticket));

        $this->attachmentManager->expects($this->exactly(count($this->ticket->getAttachments())))
            ->method('deleteAttachment')
            ->with($this->isInstanceOf('\Diamante\DeskBundle\Model\Attachment\Attachment'));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('DELETE'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticket
            ->expects($this->once())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

        $this->ticketService->deleteTicketByKey(self::DUMMY_TICKET_KEY);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testDeleteTicketByIdWhenTicketDoesNotExist()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with(self::DUMMY_TICKET_ID)
            ->will($this->returnValue(null));

        $this->ticketService->deleteTicket(self::DUMMY_TICKET_ID);
    }

    public function testLoadTicket()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('VIEW'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $this->ticketService->loadTicket(self::DUMMY_TICKET_ID);
    }

    public function testUpdateProperties()
    {
        $this->ticketRepository->expects($this->once())->method('get')->will($this->returnValue($this->ticket));

        $properties = array(
            'subject'     => 'DUMMY_SUBJECT_UPDT',
            'description' => 'DUMMY_DESC_UPDT',
            'status'      => 'open',
            'priority'    => 'high',
            'source'      => 'phone'
        );

        $this->ticket->expects($this->once())->method('updateProperties')
            ->with($this->equalTo($properties));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $command = new UpdatePropertiesCommand();
        $command->id = 1;
        $command->properties = $properties;

        $this->ticketService->updateProperties($command);
    }

    public function testUpdatePropertiesByKey()
    {
        $this->ticketRepository->expects($this->once())->method('get')->will($this->returnValue($this->ticket));

        $properties = array(
            'subject'     => 'DUMMY_SUBJECT_UPDT_BY_KEY',
            'description' => 'DUMMY_DESC_UPDT_BY_KEY',
            'status'      => 'open',
            'priority'    => 'high',
            'source'      => 'phone'
        );

        $this->ticket->expects($this->once())->method('updateProperties')
            ->with($this->equalTo($properties));

        $this->ticket->expects($this->once())->method('getId')
            ->will($this->returnValue(1));

        $this->ticketRepository->expects($this->once())->method('getByTicketKey')
            ->with(new TicketKey('DT', 1))
            ->will($this->returnValue($this->ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->authorizationService->expects($this->any())->method('isActionPermitted')
            ->with($this->anything(), $this->equalTo($this->ticket))
            ->will($this->returnValue(true));

        $command = new UpdatePropertiesCommand();
        $command->key = static::DUMMY_TICKET_KEY;
        $command->properties = $properties;

        $this->ticketService->updatePropertiesByKey($command);
    }


    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testDeleteTicketByKeyWhenTicketDoesNotExist()
    {
        $this->ticketRepository->expects($this->once())->method('getByTicketKey')->with(new TicketKey('DT', 1))
            ->will($this->returnValue(null));

        $this->ticketService->deleteTicketByKey(self::DUMMY_TICKET_KEY);
    }
}
