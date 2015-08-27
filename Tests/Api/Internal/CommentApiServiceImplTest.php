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

use Diamante\DeskBundle\Api\Command\Filter\FilterCommentsCommand;
use Diamante\DeskBundle\Api\Internal\CommentApiServiceImpl;
use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Entity\Comment;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Model\Shared\Filter\FilterPagingProperties;
use Diamante\DeskBundle\Model\Shared\Filter\PagingInfo;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Source;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketSequenceNumber;
use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

class CommentApiServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_USER_ID         = 1;
    const DUMMY_TICKET_SUBJECT      = 'Subject';
    const DUMMY_TICKET_DESCRIPTION  = 'Description';

    /**
     * @var CommentApiServiceImpl
     */
    private $service;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $commentRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Shared\Repository
     * @Mock \Diamante\DeskBundle\Model\Shared\Repository
     */
    private $ticketRepository;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\CommentFactory
     * @Mock \Diamante\DeskBundle\Model\Ticket\CommentFactory
     */
    private $commentFactory;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var \Diamante\DeskBundle\Model\Attachment\Manager
     * @Mock \Diamante\DeskBundle\Model\Attachment\Manager
     */
    private $attachmentManager;

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
     * @var \Diamante\DeskBundle\Api\ApiPagingService
     * @Mock Diamante\DeskBundle\Api\ApiPagingService
     */
    private $apiPagingService;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     * @Mock \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $registry;

    public function setUp()
    {
        MockAnnotations::init($this);
        $this->service = new CommentApiServiceImpl(
            $this->registry,
            $this->ticketRepository,
            $this->commentRepository,
            $this->commentFactory,
            $this->userService,
            $this->attachmentManager,
            $this->authorizationService
        );

        $this->service->setApiPagingService($this->apiPagingService);

        $this->_dummyTicket = new Ticket(
            new UniqueId('unique_id'),
            new TicketSequenceNumber(12),
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createAuthor(),
            $this->createAssignee(),
            new Source(Source::PHONE),
            new Priority(Priority::PRIORITY_LOW),
            new Status(Status::CLOSED)
        );
    }

    public function testCommentsFiltered()
    {
        $comments = array(
            new Comment("DUMMY_CONTENT_1", $this->_dummyTicket, $this->createAuthor(), false),
            new Comment("DUMMY_CONTENT_2", $this->_dummyTicket, $this->createAuthor(), false),
        );

        $command = new FilterCommentsCommand();
        $command->author = 'oro_1';
        $pagingInfo = new PagingInfo(1, new FilterPagingProperties());

        $this->commentRepository
            ->expects($this->once())
            ->method('filter')
            ->with($this->equalTo(array(array('author','eq','oro_1'))), $this->equalTo(new FilterPagingProperties()))
            ->will($this->returnValue(array($comments[1])));

        $this->apiPagingService
            ->expects($this->once())
            ->method('getPagingInfo')
            ->will($this->returnValue($pagingInfo));

        $retrievedComments = $this->service->listAllComments($command);

        $this->assertNotNull($retrievedComments);
        $this->assertTrue(is_array($retrievedComments));
        $this->assertNotEmpty($retrievedComments);
        $this->assertEquals($comments[1], $retrievedComments[0]);
    }

    private function createBranch()
    {
        return new Branch('DUMMY', 'DUMMY_NAME', 'DUMMY_DESC');
    }

    private function createAuthor()
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
}
