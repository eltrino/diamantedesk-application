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
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Model\User\User;
use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;

use Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

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
     * @var \Doctrine\ORM\EntityManager
     * @Mock Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     * @Mock \Doctrine\ORM\UnitOfWork
     */
    private $unitOfWork;


    /**
     * @var \Doctrine\ORM\Persisters\BasicEntityPersister
     * @Mock \Doctrine\ORM\Persisters\BasicEntityPersister
     */
    private $entityPersister;

    public function setUp()
    {
        MockAnnotations::init($this);
        $this->notificationDeliveryManager = new NotificationDeliveryManager();
        $this->service = new CommentServiceImpl($this->ticketRepository,
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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author);
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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author);

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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $ticket, $author);

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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser());

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($comment));

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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser());

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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser());

        $updatedContent = self::DUMMY_COMMENT_CONTENT . ' (edited)';

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($comment));

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
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser());
        $comment->addAttachment(new Attachment(new File('some/path/file.ext')));
        $comment->addAttachment(new Attachment(new File('some/path/file.ext')));

        $this->commentRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_COMMENT_ID))
            ->will($this->returnValue($comment));

        $this->commentRepository->expects($this->once())->method('remove')
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
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Comment has no such attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenCommentHasNoAttachment()
    {
        $comment = new Comment(self::DUMMY_COMMENT_CONTENT, $this->_dummyTicket, $this->createDiamanteUser());
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

        $this->commentRepository->expects($this->once())->method('store')->with($this->equalTo($this->comment));

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


    /**
     * @test
     */
    public function testCommentsFiltered()
    {
        $comments = array(
            new Comment("DUMMY_CONTENT_1", $this->_dummyTicket, new User),
            new Comment("DUMMY_CONTENT_2", $this->_dummyTicket, new User)
        );

        $this->commentRepository = new DoctrineGenericRepository($this->em, new ClassMetadata('Diamante\DeskBundle\Entity\Comment'));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork
            ->expects($this->atLeastOnce())
            ->method('getEntityPersister')
            ->with($this->equalTo('Diamante\DeskBundle\Entity\Comment'))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister
            ->expects($this->atLeastOnce())
            ->method('loadAll')
            ->will($this->returnValue($comments));

        $this->service = new CommentServiceImpl($this->ticketRepository, $this->commentRepository,
            $this->commentFactory, $this->userService, $this->attachmentManager, $this->securityFacade,
            $this->dispatcher, $this->processManager);

        $filtered = $this->service->filterComments($this->getCorrectFilteringParams());

        $this->assertEquals(1, count($filtered));

        $filteredComment = $filtered[0];
        $comparativeComment = $comments[0];

        $this->assertEquals($comparativeComment, $filteredComment);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid filtering constraint 'nonExistentFilteringConstraint' used. Should be one of these: andX, orX, eq, neq, gt, gte, lt, lte, isNull, in, notIn, contains
     */
    public function testExceptionThrownIfUsingIncorrectFilteringConstraint()
    {
        $comments = array(
            new Comment("DUMMY_CONTENT_1", $this->_dummyTicket, new User),
            new Comment("DUMMY_CONTENT_2", $this->_dummyTicket, new User)
        );

        $this->commentRepository = new DoctrineGenericRepository($this->em, new ClassMetadata('Diamante\DeskBundle\Entity\Comment'));

        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->unitOfWork));

        $this->unitOfWork
            ->expects($this->atLeastOnce())
            ->method('getEntityPersister')
            ->with($this->equalTo('Diamante\DeskBundle\Entity\Comment'))
            ->will($this->returnValue($this->entityPersister));

        $this->entityPersister
            ->expects($this->atLeastOnce())
            ->method('loadAll')
            ->will($this->returnValue($comments));

        $this->service = new CommentServiceImpl($this->ticketRepository, $this->commentRepository,
            $this->commentFactory, $this->userService, $this->attachmentManager, $this->securityFacade,
            $this->dispatcher, $this->processManager);

        $filtered = $this->service->filterComments($this->getIncorrectFilteringParams());

        $this->assertEquals(1, count($filtered));

        $filteredComment = $filtered[0];
        $comparativeComment = $comments[0];

        $this->assertEquals($comparativeComment, $filteredComment);
    }

    protected function getCorrectFilteringParams()
    {
        return array(
            array(
                'content',
                'eq',
                'DUMMY_CONTENT_1'
            )
        );
    }

    protected function getIncorrectFilteringParams()
    {
        return array(
            array(
                'content',
                'nonExistentFilteringConstraint',
                'DUMMY_CONTENT_1'
            )
        );
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
