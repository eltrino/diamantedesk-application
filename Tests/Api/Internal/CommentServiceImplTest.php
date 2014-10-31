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
use Diamante\DeskBundle\Api\Command\EditCommentCommand;
use Diamante\DeskBundle\Model\Ticket\Comment;
use Diamante\DeskBundle\Model\Branch\Branch;
use Diamante\DeskBundle\Api\Internal\CommentServiceImpl;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Tests\Stubs\AttachmentStub;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;
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
     * @var \Diamante\DeskBundle\Model\Shared\UserService
     * @Mock \Diamante\DeskBundle\Model\Shared\UserService
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
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     * @Mock \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $dispatcher;

    /**
     * @var \Diamante\DeskBundle\EventListener\Mail\CommentProcessManager
     * @Mock \Diamante\DeskBundle\EventListener\Mail\CommentProcessManager
     */
    private $processManager;

    public function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new CommentServiceImpl($this->ticketRepository, $this->commentRepository,
            $this->commentFactory, $this->userService, $this->attachmentManager, $this->securityFacade,
            $this->dispatcher, $this->processManager);

        $this->_dummyTicket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Source::PHONE,
            Priority::PRIORITY_LOW,
            Status::CLOSED
        );
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

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $command = new EditCommentCommand();
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
        $author  = new User;
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->userService->expects($this->once())->method('getUserById')->with($this->equalTo(self::DUMMY_USER_ID))
            ->will($this->returnValue($author));

        $this->commentFactory->expects($this->once())->method('create')->with(
            $this->equalTo(self::DUMMY_COMMENT_CONTENT),
            $this->equalTo($ticket),
            $this->equalTo($author)
        )->will($this->returnValue($comment));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $command = new EditCommentCommand();
        $command->content  = self::DUMMY_COMMENT_CONTENT;
        $command->ticket = self::DUMMY_TICKET_ID;
        $command->author = self::DUMMY_USER_ID;
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
        $author  = new User;
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author);

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->userService->expects($this->once())->method('getUserById')->with($this->equalTo(self::DUMMY_USER_ID))
            ->will($this->returnValue($author));

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

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:DiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $command = new EditCommentCommand();
        $command->content  = self::DUMMY_COMMENT_CONTENT;
        $command->ticket = self::DUMMY_TICKET_ID;
        $command->author = self::DUMMY_USER_ID;
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

        $command = new EditCommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->content = self::DUMMY_COMMENT_CONTENT;

        $this->service->updateTicketComment($command);
    }

    /**
     * @test
     */
    public function thatCommentUpdates()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, new User);

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $ticket  = $this->_dummyTicket;
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $command = new EditCommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->content = $updatedContent;
        $command->ticket  = self::DUMMY_TICKET_ID;
        $command->ticketStatus = Status::IN_PROGRESS;

        $this->service->updateTicketComment($command);

        $this->assertEquals($updatedContent, $comment->getContent());
    }

    /**
     * @test
     */
    public function thatCommentUpdatesWithAttachments()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, new User);

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($comment));

        $filesListDto = $this->attachmentInputs();

        $this->attachmentManager->expects($this->exactly(count($filesListDto)))
            ->method('createNewAttachment')
            ->with(
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->isType(\PHPUnit_Framework_Constraint_IsType::TYPE_STRING),
                $this->equalTo($comment)
            );

        $ticket  = $this->_dummyTicket;
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $comment)
            ->will($this->returnValue(true));

        $command = new EditCommentCommand();
        $command->id      = self::DUMMY_COMMENT_ID;
        $command->ticket  = self::DUMMY_TICKET_ID;
        $command->content = $updatedContent;
        $command->ticketStatus = Status::IN_PROGRESS;
        $command->attachmentsInput = $filesListDto;

        $this->service->updateTicketComment($command);
        $this->assertEquals($updatedContent, $comment->getContent());
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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, new User);
        $comment->addAttachment(new Attachment(new File('some/path/file.ext')));
        $comment->addAttachment(new Attachment(new File('some/path/file.ext')));

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->commentRepository->expects($this->once())->method('remove')
            ->with($this->equalTo($comment));

        $this->attachmentManager->expects($this->exactly(count($comment->getAttachments())))
            ->method('deleteAttachment')
            ->with($this->isInstanceOf('\Diamante\DeskBundle\Model\Attachment\Attachment'));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('DELETE'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $this->service->deleteTicketComment(self::DUMMY_COMMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Comment has no such attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenCommentHasNoAttachment()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, new User);
        $comment->addAttachment(new Attachment(new File('filename.ext')));
        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
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

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($this->comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->comment))
            ->will($this->returnValue(true));

        $removeCommentAttachmentCommand = new RemoveCommentAttachmentCommand();
        $removeCommentAttachmentCommand->attachmentId = 1;
        $removeCommentAttachmentCommand->commentId = self::DUMMY_COMMENT_ID;
        $this->service->removeAttachmentFromComment($removeCommentAttachmentCommand);
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
