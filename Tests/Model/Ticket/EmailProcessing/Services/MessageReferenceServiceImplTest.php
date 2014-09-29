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
namespace Eltrino\DiamanteDeskBundle\Tests\Model\Ticket\EmailProcessing\Services;

use Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReference;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Priority;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Source;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Comment;
use Eltrino\DiamanteDeskBundle\Model\Branch\Branch;
use Oro\Bundle\UserBundle\Entity\User;
use Eltrino\DiamanteDeskBundle\Model\Ticket\Status;
use Eltrino\EmailProcessingBundle\Infrastructure\Message\Attachment;

class MessageReferenceServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID           = 1;
    const DUMMY_TICKET_SUBJECT      = 'Subject';
    const DUMMY_TICKET_DESCRIPTION  = 'Description';
    const DUMMY_COMMENT_CONTENT     = 'dummy_comment_content';
    const DUMMY_MESSAGE_ID          = 'dummy_message_id';

    const DUMMY_FILENAME      = 'dummy_file.jpg';
    const DUMMY_FILE_CONTENT  = 'DUMMY_CONTENT';
    const DUMMY_ATTACHMENT_ID = 1;

    /**
     * @var MessageReferenceServiceImpl
     */
    private $messageReferenceService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     */
    private $messageReferenceRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     */
    private $ticketRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     */
    private $branchRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Shared\Repository
     */
    private $attachmentRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\TicketFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\TicketFactory
     */
    private $ticketFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\CommentFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\CommentFactory
     */
    private $commentFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Attachment\AttachmentFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Attachment\AttachmentFactory
     */
    private $attachmentFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Shared\UserService
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Shared\UserService
     */
    private $userService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Attachment\Services\FileStorageService
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Attachment\Services\FileStorageService
     */
    private $fileStorage;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     */
    private $ticket;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\Comment
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\Comment
     */
    private $comment;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReference
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\MessageReference
     */
    private $messageReference;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Attachment
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\Attachment
     */
    private $attachment;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->messageReferenceService = new MessageReferenceServiceImpl(
            $this->messageReferenceRepository,
            $this->ticketRepository,
            $this->branchRepository,
            $this->attachmentRepository,
            $this->ticketFactory,
            $this->commentFactory,
            $this->attachmentFactory,
            $this->userService,
            $this->fileStorage
        );
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithNoAttachments()
    {
        $branchId = 1;
        $branch = $this->createBranch();
        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo($branchId))
            ->will($this->returnValue($branch));

        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))
            ->method('getUserById')
            ->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))
            ->method('getUserById')
            ->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $this->ticketFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(self::DUMMY_TICKET_SUBJECT), $this->equalTo(self::DUMMY_TICKET_DESCRIPTION),
                $this->equalTo($branch), $this->equalTo($reporter), $this->equalTo($assignee), $this->equalTo(null), $this->equalTo(Source::EMAIL)
            )->will($this->returnValue($this->ticket));

        $this->fileStorage->expects($this->exactly(0))
            ->method('upload');

        $this->ticketRepository->expects($this->once())
            ->method('store')
            ->with($this->equalTo($this->ticket));

        $messageReference = new MessageReference(self::DUMMY_MESSAGE_ID, $this->ticket);

        $this->messageReferenceRepository->expects($this->once())
            ->method('store')
            ->with($this->equalTo($messageReference));

        $this->messageReferenceService->createTicket(
            self::DUMMY_MESSAGE_ID,
            $branchId,
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $reporterId,
            $assigneeId
        );
    }

    /**
     * @test
     */
    public function thatTicketCreatesWithAttachments()
    {
        $branchId = 1;
        $branch = $this->createBranch();
        $this->branchRepository->expects($this->once())
            ->method('get')
            ->with($this->equalTo($branchId))
            ->will($this->returnValue($branch));

        $reporterId = 2;
        $assigneeId = 3;
        $reporter = $this->createReporter();
        $assignee = $this->createAssignee();

        $this->userService->expects($this->at(0))
            ->method('getUserById')
            ->with($this->equalTo($reporterId))
            ->will($this->returnValue($reporter));

        $this->userService->expects($this->at(1))
            ->method('getUserById')
            ->with($this->equalTo($assigneeId))
            ->will($this->returnValue($assignee));

        $this->ticketFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(self::DUMMY_TICKET_SUBJECT), $this->equalTo(self::DUMMY_TICKET_DESCRIPTION),
                $this->equalTo($branch), $this->equalTo($reporter), $this->equalTo($assignee), $this->equalTo(null), $this->equalTo(Source::EMAIL)
            )->will($this->returnValue($this->ticket));

        $fileRealPath = 'dummy/file/real/path/' . self::DUMMY_FILENAME;

        $this->fileStorage->expects($this->once())->method('upload')->with(
            $this->logicalAnd(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->stringContains(self::DUMMY_FILENAME)
            ), $this->equalTo(self::DUMMY_FILE_CONTENT)
        )->will($this->returnValue($fileRealPath));

        $this->attachmentFactory->expects($this->once())->method('create')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Eltrino\DiamanteDeskBundle\Model\Attachment\File'),
                $this->callback(function($other) {
                    return MessageReferenceServiceImplTest::DUMMY_FILENAME == $other->getFilename();
                })
            )
        )->will($this->returnValue($this->attachment));

        $this->ticket->expects($this->once())->method('addAttachment')->with($this->equalTo($this->attachment));
        $this->attachmentRepository->expects($this->once())->method('store')->with($this->equalTo($this->attachment));

        $this->ticketRepository->expects($this->once())
            ->method('store')
            ->with($this->equalTo($this->ticket));

        $messageReference = new MessageReference(self::DUMMY_MESSAGE_ID, $this->ticket);

        $this->messageReferenceRepository->expects($this->once())
            ->method('store')
            ->with($this->equalTo($messageReference));

        $this->messageReferenceService->createTicket(
            self::DUMMY_MESSAGE_ID,
            $branchId,
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $reporterId,
            $assigneeId,
            null,
            null,
            $this->attachments()
        );
    }

    /**
     * @test
     */
    public function thatCommentCreatesWithNoAttachments()
    {
        $author  = $this->createAuthor();
        $authorId = 1;

        $ticket = $this->createDummyTicket();

        $this->messageReferenceRepository->expects($this->once())
            ->method('getReferenceByMessageId')
            ->with($this->equalTo(self::DUMMY_MESSAGE_ID))
            ->will($this->returnValue($this->messageReference));

        $this->messageReference->expects($this->once())
            ->method('getTicket')
            ->will($this->returnValue($ticket));

        $this->userService->expects($this->once())
            ->method('getUserById')
            ->with($this->equalTo($authorId))
            ->will($this->returnValue($author));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($this->comment));

        $this->fileStorage->expects($this->exactly(0))
            ->method('upload');

        $this->ticketRepository->expects($this->once())->method('store')
            ->with($this->equalTo($ticket));

        $this->messageReferenceService->createCommentForTicket(
            self::DUMMY_COMMENT_CONTENT, $authorId, self::DUMMY_MESSAGE_ID
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
        $authorId = 1;

        $ticket = $this->createDummyTicket();

        $this->messageReferenceRepository->expects($this->once())
            ->method('getReferenceByMessageId')
            ->with($this->equalTo(self::DUMMY_MESSAGE_ID))
            ->will($this->returnValue($this->messageReference));

        $this->messageReference->expects($this->once())
            ->method('getTicket')
            ->will($this->returnValue($ticket));

        $this->userService->expects($this->once())
            ->method('getUserById')
            ->with($this->equalTo($authorId))
            ->will($this->returnValue($author));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($this->comment));

        $fileRealPath = 'dummy/file/real/path/' . self::DUMMY_FILENAME;

        $this->fileStorage->expects($this->once())->method('upload')->with(
            $this->logicalAnd(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->stringContains(self::DUMMY_FILENAME)
            ), $this->equalTo(self::DUMMY_FILE_CONTENT)
        )->will($this->returnValue($fileRealPath));

        $this->attachmentFactory->expects($this->once())->method('create')->with(
            $this->logicalAnd(
                $this->isInstanceOf('\Eltrino\DiamanteDeskBundle\Model\Attachment\File'),
                $this->callback(function($other) {
                    return MessageReferenceServiceImplTest::DUMMY_FILENAME == $other->getFilename();
                })
            )
        )->will($this->returnValue($this->attachment));

        $this->comment->expects($this->once())->method('addAttachment')->with($this->equalTo($this->attachment));
        $this->attachmentRepository->expects($this->once())->method('store')->with($this->equalTo($this->attachment));

        $this->ticketRepository->expects($this->once())->method('store')
            ->with($this->equalTo($ticket));

        $this->messageReferenceService->createCommentForTicket(
            self::DUMMY_COMMENT_CONTENT, $authorId, self::DUMMY_MESSAGE_ID, $this->attachments()
        );

        $this->assertCount(1, $ticket->getComments());
        $this->assertEquals($this->comment, $ticket->getComments()->get(0));
    }

    private function createBranch()
    {
        return new Branch('DUMMY_NAME', 'DUMMY_DESC');
    }

    private function createReporter()
    {
        return new User();
    }

    private function createAssignee()
    {
        return new User();
    }

    private function createAuthor()
    {
        return new User();
    }

    private function createDummyTicket()
    {
        return new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Source::PHONE,
            Priority::PRIORITY_MEDIUM,
            Status::CLOSED
        );
    }

    private function attachments()
    {
        return array(new Attachment(self::DUMMY_FILENAME, self::DUMMY_FILE_CONTENT));
    }
}
