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
namespace Diamante\DeskBundle\Tests\Infrastructure\Ticket\EmailProcessing;

use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Infrastructure\Ticket\EmailProcessing\TicketStrategy;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use \Diamante\DeskBundle\Tests\EntityBuilderTrait;

class TicketStrategyTest extends \PHPUnit_Framework_TestCase
{
    use EntityBuilderTrait;

    const DEFAULT_BRANCH_ID  = 1;
    const DUMMY_BRANCH_ID    = 1;

    const DUMMY_UNIQUE_ID    = 'dummy_unique_id';
    const DUMMY_MESSAGE_ID   = 'dummy_message_id';
    const DUMMY_SUBJECT      = 'dummy_subject';
    const DUMMY_CONTENT      = 'dummy_content';
    const DUMMY_MESSAGE_FROM = 'from@gmail.com';
    const DUMMY_MESSAGE_TO   = 'to@gmail.com';

    const DUMMY_REFERENCE    = 'dummy_reference';

    /**
     * @var TicketStrategy
     */
    private $ticketStrategy;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceService
     * @Mock \Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceService
     */
    private $messageReferenceService;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $emailProcessingSettings;

    /**
     * @var \Diamante\DeskBundle\Api\Internal\WatchersServiceImpl
     * @Mock \Diamante\DeskBundle\Api\Internal\WatchersServiceImpl
     */
    private $watcherService;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\UserManager
     * @Mock \Oro\Bundle\UserBundle\Entity\UserManager
     */
    private $oroUserManager;

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    /**
     * @var \Diamante\UserBundle\Api\UserService
     * @Mock \Diamante\UserBundle\Api\UserService
     */
    private $userService;

    /**
     * @var \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     * @Mock \Diamante\DeskBundle\Infrastructure\Persistence\DoctrineGenericRepository
     */
    private $branchRepository;

    /**
     * @var \Diamante\DeskBundle\Entity\Branch
     * @Mock \Diamante\DeskBundle\Entity\Branch
     */
    private $defaultBranch;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     * @Mock \Diamante\DeskBundle\Model\Ticket\EmailProcessing\MessageReferenceRepository
     */
    private $messageReferenceRepository;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->ticketStrategy = new TicketStrategy(
            $this->messageReferenceService,
            $this->emailProcessingSettings,
            $this->watcherService,
            $this->oroUserManager,
            $this->configManager,
            $this->userService,
            $this->branchRepository,
            $this->messageReferenceRepository
        );
    }

    public function testProcessWhenDiamanteUserExists()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $diamanteUser = $this->createReporter(1);

        $this->userService->expects($this->once())
            ->method('getUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('diamante_desk.default_branch'))
            ->will($this->returnValue(1));

        $this->branchRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->defaultBranch));

        $this->defaultBranch
            ->expects($this->any())
            ->method('getDefaultAssigneeId')
            ->will($this->returnValue($assigneeId));

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with(
                $message,
                self::DEFAULT_BRANCH_ID,
                $diamanteUser,
                $assigneeId,
                null
            );

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenDiamanteUserNotExists()
    {
        $dummyFrom = $this->getDummyFrom();
        $diamanteUser = new User(1, 'diamante');
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $dummyFrom, self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('diamante_desk.default_branch'))
            ->will($this->returnValue(1));

        $this->branchRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->defaultBranch));

        $this->defaultBranch
            ->expects($this->any())
            ->method('getDefaultAssigneeId')
            ->will($this->returnValue($assigneeId));

        $this->userService->expects($this->once())
            ->method('getUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue(null));

        $this->userService->expects($this->once())
            ->method('createDiamanteUser')
            ->will($this->returnValue(1));

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with(
                $message,
                self::DEFAULT_BRANCH_ID,
                $diamanteUser,
                $assigneeId,
                null
            );

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithoutReferenceWithDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $diamanteUser = $this->createReporter(1);

        $this->userService->expects($this->once())
            ->method('getUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('diamante_desk.default_branch'))
            ->will($this->returnValue(1));

        $this->branchRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->defaultBranch));

        $this->defaultBranch
            ->expects($this->any())
            ->method('getDefaultAssigneeId')
            ->will($this->returnValue($assigneeId));

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with(
                $message,
                self::DEFAULT_BRANCH_ID,
                $diamanteUser,
                $assigneeId,
                null
            );

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithoutReferenceWithoutDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $diamanteUser = $this->createReporter(1);

        $this->userService->expects($this->once())
            ->method('getUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));

        $this->configManager
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('diamante_desk.default_branch'))
            ->will($this->returnValue(1));

        $this->branchRepository
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo(1))
            ->will($this->returnValue($this->defaultBranch));

        $this->defaultBranch
            ->expects($this->any())
            ->method('getDefaultAssigneeId')
            ->will($this->returnValue($assigneeId));

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with(
                $message,
                self::DEFAULT_BRANCH_ID,
                $diamanteUser,
                $assigneeId,
                null
            );

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithReference()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO, self::DUMMY_REFERENCE);

        $diamanteUser = $this->createReporter(1);

        $this->userService->expects($this->once())
            ->method('getUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));

        $this->messageReferenceRepository->expects($this->once())
            ->method('getReferenceByMessageId')
            ->with(
                $this->equalTo(self::DUMMY_REFERENCE)
            )
            ->will($this->returnValue($this->getMessageReference()));

        $reporter = $this->createReporter($diamanteUser->getId());

        $this->messageReferenceService->expects($this->once())
            ->method('createCommentForTicket')
            ->with($this->equalTo($message->getContent()), $reporter, $message->getReference());

        $this->ticketStrategy->process($message);
    }
}
