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

use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\EmailProcessingBundle\Model\Service\EmailFilterService;

class MessageReferenceServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID = 1;
    const SUBJECT = 'Subject';
    const DESCRIPTION = 'Description';
    const DUMMY_COMMENT_CONTENT = 'dummy_comment_content';
    const DUMMY_CLEANED_COMMENT_CONTENT = "<p>dummy<em>comment</em>content</p>\n";
    const DUMMY_MESSAGE_ID = 'dummy_message_id';

    const DUMMY_FILENAME = 'dummy_file.jpg';
    const DUMMY_FILE_CONTENT = 'DUMMY_CONTENT';
    const DUMMY_ATTACHMENT_ID = 1;
    const DUMMY_REPORTER_ID = 1;

    /**
     * @var MessageReferenceServiceImpl
     */
    private $messageReferenceService;

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
    public function thatCommentContainsOnlyLastResponse()
    {
        $author = $this->createAuthor();

        $ticket = $this->createDummyTicket();

        $this->messageReferenceRepository->expects($this->once())
            ->method('getReferenceByMessageId')
            ->with($this->equalTo(self::DUMMY_MESSAGE_ID))
            ->will($this->returnValue($this->messageReference));

        $this->messageReference->expects($this->once())
            ->method('getTicket')
            ->will($this->returnValue($ticket));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_CLEANED_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($this->comment));

        $this->ticketRepository->expects($this->once())->method('store')
            ->with($this->equalTo($ticket));

        $rawComment = self::DUMMY_COMMENT_CONTENT
            . MessageReferenceServiceImpl::DELIMITER_LINE
            . self::DUMMY_COMMENT_CONTENT
            . MessageReferenceServiceImpl::DELIMITER_LINE
            . self::DUMMY_COMMENT_CONTENT;

        $emailFilterService = new EmailFilterService($rawComment);
        $cleanedContent = $emailFilterService->recognizeUsefulContent($rawComment);

        $this->messageReferenceService->createCommentForTicket(
            $cleanedContent, (string)$author, self::DUMMY_MESSAGE_ID
        );
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
}
