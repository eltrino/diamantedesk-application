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

use Diamante\DeskBundle\Api\Command\AddCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\UpdateCommentCommand;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;
use Diamante\DeskBundle\Model\Attachment\File;
use Diamante\DeskBundle\Model\Attachment\Attachment;
use Diamante\DeskBundle\Api\Command\CommentCommand;
use Diamante\DeskBundle\Model\Ticket\Comment;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Api\Internal\CommentServiceImpl;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;

class CommentServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_COMMENT_CONTENT = 'Content';
    const DUMMY_TICKET_ID       = 1;
    const DUMMY_USER_ID         = 1;
    const DUMMY_COMMENT_ID      = 1;
    const DUMMY_TICKET_SUBJECT      = 'Subject';
    const DUMMY_TICKET_DESCRIPTION  = 'Description';
    const DUMMY_FILENAME        = 'dummy-filename.ext';
    const DUMMY_FILE_CONTENT    = 'DUMMY_CONTENT';

    /**
     * @var CommentServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $commentRepository;

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
     * @var \Diamante\DeskBundle\Entity\Comment
     * @Mock \Diamante\DeskBundle\Entity\Comment
     */
    private $comment;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Ticket
     */
    protected $_dummyTicket;

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
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     * @Mock \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $registry;

    /**
     * @var \Doctrine\ORM\EntityManager
     * @Mock \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        MockAnnotations::init($this);
        $this->notificationDeliveryManager = new NotificationDeliveryManager();
        $this->service = new CommentServiceImpl(
            $this->registry,
            $this->ticketRepository,
            $this->commentRepository,
            $this->commentFactory,
            $this->userService,
            $this->attachmentManager,
            $this->authorizationService,
            $this->dispatcher,
            $this->notificationDeliveryManager,
            $this->notifier
        );

        $this->_dummyTicket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(12),
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED)
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Comment loading failed, comment not found.
     */
    public function thatCommentRetrievesThrowsExceptionWhenNotFound()
    {
        $this->commentRepository->expects($this->once())->method('get')->with(self::DUMMY_COMMENT_ID)
            ->will($this->returnValue(null));
        $this->service->loadComment(self::DUMMY_COMMENT_ID);
    }

    /**
     * @test
     */
    public function thatCommentRetrieves()
    {
        $ticket  = $this->_dummyTicket;
        $author  = new User(self::DUMMY_USER_ID, User::TYPE_DIAMANTE);
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author, false);
        $this->commentRepository->expects($this->once())->method('get')->with(self::DUMMY_COMMENT_ID)
            ->will($this->returnValue($comment));
        $this->authorizationService->expects($this->once())->method('isActionPermitted')
            ->with('VIEW', $comment)->will($this->returnValue(true));

        $result = $this->service->loadComment(self::DUMMY_COMMENT_ID);

        $this->assertEquals($comment, $result);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatCommentPostThrowsExceptionWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $command = new CommentCommand();
        $command->content  = self::DUMMY_COMMENT_CONTENT;
        $command->ticket = self::DUMMY_TICKET_ID;
        $command->author = self::DUMMY_USER_ID;

        $this->service->postNewCommentForTicket($command);
    }

    /**
     * @test
     */
    public function thatCommentPosts()
    {
        $ticket  = $this->_dummyTicket;
        $author  = $this->createDiamanteUser();
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author, false);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($comment));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $command = new CommentCommand();
        $command->content  = self::DUMMY_COMMENT_CONTENT;
        $command->ticket = self::DUMMY_TICKET_ID;
        $command->author = $author;
        $command->ticketStatus = Status::IN_PROGRESS;

        $this->service->postNewCommentForTicket($command);

        $this->assertCount(1, $ticket->getComments());
        $this->assertEquals($comment, $ticket->getComments()->get(0));
    }

    /**
     * @test
     */
    public function thatCommentPostsWithWithAttachments()
    {
        $ticket  = $this->_dummyTicket;
        $author  = $this->createDiamanteUser();
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author, false);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($comment));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $attachmentInputs = $this->attachmentInputs();

        $this->attachmentManager->expects($this->exactly(count($attachmentInputs)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($comment)
            );

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $command = new CommentCommand();
        $command->content  = self::DUMMY_COMMENT_CONTENT;
        $command->ticket = self::DUMMY_TICKET_ID;
        $command->author = $author;
        $command->ticketStatus = Status::IN_PROGRESS;
        $command->attachmentsInput = $attachmentInputs;

        $this->service->postNewCommentForTicket($command);

        $this->assertCount(1, $ticket->getComments());
        $this->assertEquals($comment, $ticket->getComments()->get(0));
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Comment loading failed, comment not found.
     */
    public function thatCommentUpdateThrowsExceptionIfCommentDoesNotExists()
    {
        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue(null));

        $command = new CommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->content = self::DUMMY_COMMENT_CONTENT;

        $this->service->updateTicketComment($command);
    }

    /**
     * @test
     */
    public function thatCommentUpdates()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->em
            ->expects($this->any())
            ->method('persist');

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $command = new CommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->content = $updatedContent;
        $command->ticketStatus = Status::IN_PROGRESS;

        $this->service->updateTicketComment($command);

        $this->assertEquals($updatedContent, $comment->getContent());
        $this->assertEquals(Status::IN_PROGRESS, $comment->getTicket()->getStatus()->getValue());
    }

    /**
     * @test
     */
    public function thatCommentUpdatesWithAttachments()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->em
            ->expects($this->any())
            ->method('persist');

        $filesListDto = $this->attachmentInputs();

        $this->attachmentManager->expects($this->exactly(count($filesListDto)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($comment)
            );

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $comment)
            ->will($this->returnValue(true));

        $command = new CommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->content = $updatedContent;
        $command->ticket  = self::DUMMY_TICKET_ID;
        $command->ticketStatus = Status::IN_PROGRESS;
        $command->attachmentsInput = $filesListDto;

        $this->service->updateTicketComment($command);
        $this->assertEquals($updatedContent, $comment->getContent());
        $this->assertEquals(Status::IN_PROGRESS, $comment->getTicket()->getStatus()->getValue());
    }

    /**
     * @test
     */
    public function thatCommentUpdatesV2()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->em
            ->expects($this->any())
            ->method('persist');

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $comment)
            ->will($this->returnValue(true));

        $command = new UpdateCommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->content = $updatedContent;
        $command->ticketStatus = Status::IN_PROGRESS;

        $this->service->updateCommentContentAndTicketStatus($command);

        $this->assertEquals($updatedContent, $comment->getContent());
        $this->assertEquals(Status::IN_PROGRESS, $comment->getTicket()->getStatus()->getValue());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Comment loading failed, comment not found.
     */
    public function thatCommentDeleteThrowsExceptionIfCommentDoesNotExists()
    {
        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue(null));

        $this->service->deleteTicketComment(self::DUMMY_COMMENT_ID);
    }

    /**
     * @test
     */
    public function thatCommentDeletes()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);
        $comment->addAttachment(new Attachment(new File('some/path/file.ext')));
        $comment->addAttachment(new Attachment(new File('some/path/file.ext')));

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->em
            ->expects($this->any())
            ->method('remove')
            ->with($this->equalTo($comment));

        $this->attachmentManager->expects($this->exactly(count($comment->getAttachments())))
            ->method('deleteAttachment')
            ->with($this->isInstanceOf('\Diamante\DeskBundle\Model\Attachment\Attachment'));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('DELETE'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $this->service->deleteTicketComment(self::DUMMY_COMMENT_ID);
    }

    /**
     * @test
     */
    public function thatAddsCommentAttachment()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);
        $attachmentInputs = $this->attachmentInputs();

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->attachmentManager->expects($this->exactly(count($attachmentInputs)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($comment)
            );

        $this->registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->equalTo($comment));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $command = new AddCommentAttachmentCommand();
        $command->attachmentsInput = $attachmentInputs;
        $command->commentId        = self::DUMMY_COMMENT_ID;
        $this->service->addCommentAttachment($command);
    }

    /**
     * @test
     */
    public function thatListsCommentAttachment()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);
        $a1 = new Attachment(new File('some/path/file.ext'));
        $a2 = new Attachment(new File('some/path/file.ext'));
        $comment->addAttachment($a1);
        $comment->addAttachment($a2);

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('VIEW'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $attachments = $this->service->listCommentAttachment(self::DUMMY_COMMENT_ID);

        $this->assertNotNull($attachments);
        $this->assertCount(2, $attachments);
        $this->assertContains($a1, $attachments);
        $this->assertContains($a2, $attachments);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Comment has no such attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenCommentHasNoAttachment()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser(), false);
        $comment->addAttachment(new Attachment(new File('filename.ext')));
        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $removeCommentAttachmentCommand = new RemoveCommentAttachmentCommand();
        $removeCommentAttachmentCommand->attachmentId = 1;
        $removeCommentAttachmentCommand->commentId = self::DUMMY_COMMENT_ID;
        $this->service->removeAttachmentFromComment($removeCommentAttachmentCommand);
    }

    /**
     * @test
     */
    public function thatAttachmentRemovesFromComment()
    {
        $attachment = new Attachment(new File('some/path/file.ext'));
        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($this->comment));

        $this->comment->expects($this->once())->method('getAttachment')->with($this->equalTo(1))
            ->will($this->returnValue($attachment));

        $this->comment->expects($this->once())->method('removeAttachment')->with($this->equalTo($attachment));

        $this->attachmentManager->expects($this->once())->method('deleteAttachment')
            ->with($this->equalTo($attachment));

        $this->registry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->em));

        $this->em
            ->expects($this->any())
            ->method('persist');

        $this->authorizationService
            ->expects($this->once())
            ->method('isActionPermitted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->comment))
            ->will($this->returnValue(true));

        $removeCommentAttachmentCommand = new RemoveCommentAttachmentCommand();
        $removeCommentAttachmentCommand->attachmentId = 1;
        $removeCommentAttachmentCommand->commentId = self::DUMMY_COMMENT_ID;
        $this->service->removeAttachmentFromComment($removeCommentAttachmentCommand);
    }

    private function createBranch()
    {
        return new Branch('DUMM', 'DUMMY_NAME', 'DUMYY_DESC');
    }

    private function createReporter()
    {
        return new User(self::DUMMY_USER_ID, User::TYPE_DIAMANTE);
    }

    private function createAssignee()
    {
        return $this->createOroUser();
    }

    private function createOroUser()
    {
        return new OroUser();
    }

    private function createDiamanteUser()
    {
        return $this->createReporter();
    }

    /**
     * @return array of AttachmentInput
     */
    private function attachmentInputs()
    {
        $attachmentInput = new AttachmentInput();
        $attachmentInput->setFilename(self::DUMMY_FILENAME);
        $attachmentInput->setContent(self::DUMMY_FILE_CONTENT);
        return array($attachmentInput);
    }
}
