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
namespace Diamante\DeskBundle\Tests\Model\Ticket\EmailProcessing\Services;

use Diamante\DeskBundle\Entity\MessageReference;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Branch\Branch;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\EmailProcessingBundle\Infrastructure\Message\Attachment;

class MessageReferenceServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID           = 1;
    const SUBJECT      = 'Subject';
    const DESCRIPTION  = 'Description';
    const DUMMY_COMMENT_CONTENT     = 'dummy_comment_content';
    const DUMMY_MESSAGE_ID          = 'dummy_message_id';

    const DUMMY_FILENAME      = 'dummy_file.jpg';
    const DUMMY_FILE_CONTENT  = 'DUMMY_CONTENT';
    const DUMMY_ATTACHMENT_ID = 1;
    const DUMMY_REPORTER_ID   = 1;

    /**
     * @var MessageReferenceServiceImpl
     */
    private $messageReferenceService;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     * @Mock \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\TicketBuilder
     * @Mock \Diamante\DeskBundle\Model\Ticket\TicketBuilder
     */
    private $ticketBuilder;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\CommentFactory
     * @Mock \Diamante\DeskBundle\Model\Ticket\CommentFactory
     */
    private $commentFactory;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock \Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Manager
     * @Mock \Diamante\DeskBundle\Model\Attachment\Manager
     */
    private $attachmentManager;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Ticket
     * @Mock \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    private $ticket;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Comment
     * @Mock \Diamante\DeskBundle\Model\Ticket\Comment
     */
    private $comment;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference
     * @Mock \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReference
     */
    private $messageReference;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     * @Mock Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $dispatcher;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager
     * @Mock Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager
     */
    private $notificationDeliveryManager;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Notifier
     * @Mock Diamante\DeskBundle\Model\Ticket\Notifications\Notifier
     */
    private $notifier;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     * @Mock Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->messageReferenceService = new MessageReferenceServiceImpl(
            $this->em,
            $this->messageReferenceRepository,
            $this->ticketRepository,
            $this->ticketBuilder,
            $this->commentFactory,
            $this->userService,
            $this->attachmentManager,
            $this->dispatcher,
            $this->notificationDeliveryManager,
            $this->notifier,
            $this->logger
        );
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithNoAttachments()
    {
        $branchId = 1;
        $assigneeId = 3;

        $reporter = $this->createReporter();
        $this->ticketBuilder->expects($this->once())->method('setSubject')->with(self::SUBJECT)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setDescription')->with(self::DESCRIPTION)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setBranchId')->with($branchId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setReporter')->with((string)$reporter)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setAssigneeId')->with($assigneeId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setSource')->with(Source::EMAIL)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('build')->will($this->returnValue($this->ticket));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $messageReference = new MessageReference(self::DUMMY_MESSAGE_ID, $this->ticket);

        $this->messageReferenceRepository->expects($this->once())->method('store')
            ->with($this->equalTo($messageReference));

        $this->ticket
            ->expects($this->atLeastOnce())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

        $this->messageReferenceService->createTicket(
            self::DUMMY_MESSAGE_ID,
            $branchId,
            self::SUBJECT,
            self::DESCRIPTION,
            (string)$reporter,
            $assigneeId
        );
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithAttachments()
    {
        $branchId = 1;
        $assigneeId = 3;

        $reporter = $this->createReporter();

        $this->ticketBuilder->expects($this->once())->method('setSubject')->with(self::SUBJECT)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setDescription')->with(self::DESCRIPTION)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setBranchId')->with($branchId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setReporter')->with((string)$reporter)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setAssigneeId')->with($assigneeId)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('setSource')->with(Source::EMAIL)->will($this->returnValue($this->ticketBuilder));
        $this->ticketBuilder->expects($this->once())->method('build')->will($this->returnValue($this->ticket));

        $this->attachmentManager->expects($this->once())->method('createNewAttachment')
            ->with(
                $this->equalTo(self::DUMMY_FILENAME),
                $this->equalTo(self::DUMMY_FILE_CONTENT),
                $this->equalTo($this->ticket)
            );

        $this->ticketRepository->expects($this->once())
            ->method('store')
            ->with($this->equalTo($this->ticket));

        $messageReference = new MessageReference(self::DUMMY_MESSAGE_ID, $this->ticket);

        $this->messageReferenceRepository->expects($this->once())
            ->method('store')
            ->with($this->equalTo($messageReference));

        $this->ticket
            ->expects($this->atLeastOnce())
            ->method('getRecordedEvents')
            ->will($this->returnValue(array()));

        $this->messageReferenceService->createTicket(
            self::DUMMY_MESSAGE_ID,
            $branchId,
            self::SUBJECT,
            self::DESCRIPTION,
            $reporter,
            $assigneeId,
            $this->attachments()
        );
    }

    /**
     * @test
     */
    public function thatCommentCreatesWithNoAttachments()
    {
        $author  = $this->createAuthor();

        $ticket = $this->createDummyTicket();

        $this->messageReferenceRepository->expects($this->once())
            ->method('getReferenceByMessageId')
            ->with($this->equalTo(self::DUMMY_MESSAGE_ID))
            ->will($this->returnValue($this->messageReference));

        $this->messageReference->expects($this->once())
            ->method('getTicket')
            ->will($this->returnValue($ticket));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($this->comment));

        $this->ticketRepository->expects($this->once())->method('store')
            ->with($this->equalTo($ticket));

        $this->messageReferenceService->createCommentForTicket(
            self::DUMMY_COMMENT_CONTENT, (string)$author, self::DUMMY_MESSAGE_ID
        );

        $this->assertCount(1, $ticket->getComments());
        $this->assertEquals($this->comment, $ticket->getComments()->get(0));
    }

    /**
     * @test
     */
    public function thatCommentCreatesWithAttachments()
    {
        $author  = $this->createAuthor();

        $ticket = $this->createDummyTicket();

        $this->messageReferenceRepository->expects($this->once())
            ->method('getReferenceByMessageId')
            ->with($this->equalTo(self::DUMMY_MESSAGE_ID))
            ->will($this->returnValue($this->messageReference));

        $this->messageReference->expects($this->once())
            ->method('getTicket')
            ->will($this->returnValue($ticket));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($this->comment));

        $this->attachmentManager->expects($this->once())->method('createNewAttachment')
            ->with(
                $this->equalTo(self::DUMMY_FILENAME),
                $this->equalTo(self::DUMMY_FILE_CONTENT),
                $this->equalTo($this->comment)
            );

        $this->ticketRepository->expects($this->once())->method('store')
            ->with($this->equalTo($ticket));

        $this->messageReferenceService->createCommentForTicket(
            self::DUMMY_COMMENT_CONTENT, (string)$author, self::DUMMY_MESSAGE_ID, $this->attachments()
        );

        $this->assertCount(1, $ticket->getComments());
        $this->assertEquals($this->comment, $ticket->getComments()->get(0));
    }

    private function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMMY_DESC');
    }

    private function createReporter()
    {
        return new User(self::DUMMY_REPORTER_ID, User::TYPE_DIAMANTE);
    }

    private function createAssignee()
    {
        return new OroUser();
    }

    private function createAuthor()
    {
        return $this->createReporter();
    }

    private function createDummyTicket()
    {
        return new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(null),
            self::SUBJECT,
            self::DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_MEDIUM),
            new Status(Status::CLOSED)
        );
    }

    private function attachments()
    {
        return array(new Attachment(self::DUMMY_FILENAME, self::DUMMY_FILE_CONTENT));
    }
}
