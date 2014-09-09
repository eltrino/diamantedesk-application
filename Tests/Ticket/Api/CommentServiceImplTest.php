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
namespace Eltrino\DiamanteDeskBundle\Tests\Ticket\Api;

use Doctrine\ORM\EntityManager;
use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\AttachmentInput;
use Eltrino\DiamanteDeskBundle\Attachment\Model\File;
use Eltrino\DiamanteDeskBundle\EltrinoDiamanteDeskBundle;
use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Comment;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Form\Command\EditCommentCommand;
use Eltrino\DiamanteDeskBundle\Ticket\Api\CommentServiceImpl;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Source;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Ticket;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Priority;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

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
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository
     */
    private $ticketRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Model\CommentRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Model\CommentRepository
     */
    private $commentRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\CommentFactory
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\CommentFactory
     */
    private $commentFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService
     */
    private $userService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService
     */
    private $attachmentService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Comment
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\Comment
     */
    private $comment;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Ticket
     */
    protected $_dummyTicket;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    public function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new CommentServiceImpl($this->ticketRepository, $this->commentRepository,
            $this->commentFactory, $this->userService, $this->attachmentService, $this->securityFacade);

        $this->_dummyTicket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Priority::DEFAULT_PRIORITY,
            Source::PHONE,
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
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $this->service->postNewCommentForTicket(self::DUMMY_COMMENT_CONTENT, self::DUMMY_TICKET_ID, self::DUMMY_USER_ID);
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
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $this->service->postNewCommentForTicket(self::DUMMY_COMMENT_CONTENT, self::DUMMY_TICKET_ID, self::DUMMY_USER_ID);

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

        $this->attachmentService->expects($this->once())->method('createAttachmentsForItHolder')
            ->with($this->equalTo($attachmentInputs), $this->equalTo($comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('CREATE'), $this->equalTo('Entity:EltrinoDiamanteDeskBundle:Comment'))
            ->will($this->returnValue(true));

        $this->service->postNewCommentForTicket(
            self::DUMMY_COMMENT_CONTENT,
            self::DUMMY_TICKET_ID,
            self::DUMMY_USER_ID,
            $attachmentInputs
        );

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

        $this->service->updateTicketComment(self::DUMMY_COMMENT_ID, self::DUMMY_COMMENT_CONTENT);
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

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($comment))
            ->will($this->returnValue(true));

        $this->service->updateTicketComment(self::DUMMY_COMMENT_ID, $updatedContent);

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

        $this->attachmentService->expects($this->once())->method('createAttachmentsForItHolder')
            ->with($this->equalTo($filesListDto), $this->equalTo($comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $comment)
            ->will($this->returnValue(true));

        $this->service->updateTicketComment(self::DUMMY_COMMENT_ID, $updatedContent, $filesListDto);
        $this->assertEquals($updatedContent, $comment->getContent());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Comment loading failed, comment not found.
     */
    public function thtCommentDeleteThrowsExceptionIfCommentDoesNotExists()
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

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->commentRepository->expects($this->once())->method('remove')
            ->with($this->equalTo($comment));

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

        $this->service->removeAttachmentFromComment(self::DUMMY_COMMENT_ID, 1);
    }

    /**
     * @test
     */
    public function thatAttachmentRemovesFromTicket()
    {
        $attachment = new Attachment(new File('filename.ext'));
        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($this->comment));

        $this->comment->expects($this->once())->method('getAttachment')->with($this->equalTo(1))
            ->will($this->returnValue($attachment));

        $this->comment->expects($this->once())->method('removeAttachment')->with($this->equalTo($attachment));

        $this->attachmentService->expects($this->once())->method('removeAttachmentFromItHolder')
            ->with($this->equalTo($attachment));

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($this->comment));

        $this->securityFacade
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo('EDIT'), $this->equalTo($this->comment))
            ->will($this->returnValue(true));

        $this->service->removeAttachmentFromComment(self::DUMMY_COMMENT_ID, 1);
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
