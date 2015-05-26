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

use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\EmailProcessingBundle\Model\Message;
use Diamante\UserBundle\Entity\DiamanteUser;
use Diamante\UserBundle\Model\User;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Infrastructure\Ticket\EmailProcessing\TicketStrategy;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class TicketStrategyTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_BRANCH_ID  = '1';
    const DUMMY_BRANCH_ID    = '2';

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
     * @var \Diamante\DeskBundle\Api\BranchEmailConfigurationService
     * @Mock \Diamante\DeskBundle\Api\BranchEmailConfigurationService
     */
    private $branchEmailConfigurationService;

    /**
     * @var \Diamante\UserBundle\Infrastructure\DiamanteUserRepository
     * @Mock \Diamante\UserBundle\Infrastructure\DiamanteUserRepository
     */
    private $diamanteUserRepository;

    /**
     * @var \Diamante\UserBundle\Infrastructure\DiamanteUserFactory
     * @Mock \Diamante\UserBundle\Infrastructure\DiamanteUserFactory
     */
    private $diamanteUserFactory;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $emailProcessingSettings;

    /**
     * @var \Diamante\DeskBundle\EventListener\TicketNotificationsSubscriber
     * @Mock \Diamante\DeskBundle\EventListener\TicketNotificationsSubscriber
     */
    private $ticketNotificationsSubscriber;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcher
     * @Mock \Symfony\Component\EventDispatcher\EventDispatcher
     */
    private $eventDispatcher;

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

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->ticketStrategy = new TicketStrategy(
            $this->messageReferenceService,
            $this->branchEmailConfigurationService,
            $this->diamanteUserRepository,
            $this->diamanteUserFactory,
            $this->emailProcessingSettings,
            $this->ticketNotificationsSubscriber,
            $this->eventDispatcher,
            $this->watcherService,
            $this->oroUserManager,
            $this->configManager
        );
    }

    public function testProcessWhenDiamanteUserExists()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $diamanteUser = $this->getDiamanteUser();

        $this->diamanteUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));


        $reporter = $this->getReporter($diamanteUser->getId());

        preg_match('/@(.*)/', self::DUMMY_MESSAGE_FROM, $output);
        $customerDomain = $output[1];

        $this->branchEmailConfigurationService->expects($this->once())
            ->method('getConfigurationBySupportAddressAndCustomerDomain')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_TO),
                $this->equalTo($customerDomain)
            )->will($this->returnValue(null));

        $this->branchEmailConfigurationService
            ->expects($this->once())
            ->method('getBranchDefaultAssignee')
            ->with($this->equalTo(1))
            ->will($this->returnValue(1));

        $this->emailProcessingSettings->expects($this->once())
            ->method('getDefaultBranchId')
            ->will($this->returnValue(self::DEFAULT_BRANCH_ID));


        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($message->getMessageId()), self::DEFAULT_BRANCH_ID, $message->getSubject(), $message->getContent(),
                $reporter, $assigneeId);

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenDiamanteUserNotExists()
    {
        $dummyFrom = $this->getDummyFrom();
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $dummyFrom, self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;

        $this->diamanteUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue(null));

        $diamanteUser = new DiamanteUser('test_email', $dummyFrom->getFirstName(), $dummyFrom->getLastName());

        $this->diamanteUserFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));

        $this->diamanteUserRepository->expects($this->once())
            ->method('store')
            ->with(
                $this->equalTo($diamanteUser)
            );

        $reporter = new User($diamanteUser->getId(), User::TYPE_DIAMANTE);

        preg_match('/@(.*)/', self::DUMMY_MESSAGE_FROM, $output);
        $customerDomain = $output[1];

        $this->branchEmailConfigurationService->expects($this->once())
            ->method('getConfigurationBySupportAddressAndCustomerDomain')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_TO),
                $this->equalTo($customerDomain)
            )->will($this->returnValue(null));

        $this->emailProcessingSettings->expects($this->once())
            ->method('getDefaultBranchId')
            ->will($this->returnValue(self::DEFAULT_BRANCH_ID));


        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($message->getMessageId()), self::DEFAULT_BRANCH_ID, $message->getSubject(), $message->getContent(),
                $reporter, $assigneeId);

        $this->branchEmailConfigurationService
            ->expects($this->once())
            ->method('getBranchDefaultAssignee')
            ->with($this->equalTo(1))
            ->will($this->returnValue(1));

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithoutReferenceWithDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $diamanteUser = $this->getDiamanteUser();

        $this->diamanteUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));


        $reporter = $this->getReporter($diamanteUser->getId());

        preg_match('/@(.*)/', self::DUMMY_MESSAGE_FROM, $output);
        $customerDomain = $output[1];

        $this->branchEmailConfigurationService->expects($this->once())
            ->method('getConfigurationBySupportAddressAndCustomerDomain')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_TO),
                $this->equalTo($customerDomain)
            )->will($this->returnValue(null));

        $this->emailProcessingSettings->expects($this->once())
            ->method('getDefaultBranchId')
            ->will($this->returnValue(self::DEFAULT_BRANCH_ID));


        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($message->getMessageId()), self::DEFAULT_BRANCH_ID, $message->getSubject(), $message->getContent(),
                $reporter, $assigneeId);

        $this->branchEmailConfigurationService
            ->expects($this->once())
            ->method('getBranchDefaultAssignee')
            ->with($this->equalTo($assigneeId))
            ->will($this->returnValue(1));

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithoutReferenceWithoutDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $diamanteUser = $this->getDiamanteUser();

        $this->diamanteUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));


        $reporter = $this->getReporter($diamanteUser->getId());

        preg_match('/@(.*)/', self::DUMMY_MESSAGE_FROM, $output);
        $customerDomain = $output[1];

        $this->branchEmailConfigurationService->expects($this->once())
            ->method('getConfigurationBySupportAddressAndCustomerDomain')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_TO),
                $this->equalTo($customerDomain)
            )->will($this->returnValue(self::DUMMY_BRANCH_ID));

        $this->branchEmailConfigurationService
            ->expects($this->once())
            ->method('getBranchDefaultAssignee')
            ->with($this->equalTo(2))
            ->will($this->returnValue(1));

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($message->getMessageId()), self::DUMMY_BRANCH_ID, $message->getSubject(), $message->getContent(),
                $reporter, $assigneeId);

        $this->ticketStrategy->process($message);
    }


    public function testProcessWhenMessageWithReference()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, $this->getDummyFrom(), self::DUMMY_MESSAGE_TO, self::DUMMY_REFERENCE);

        $diamanteUser = $this->getDiamanteUser();

        $this->diamanteUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($diamanteUser));


        $reporter = $this->getReporter($diamanteUser->getId());

        $this->messageReferenceService->expects($this->once())
            ->method('createCommentForTicket')
            ->with($this->equalTo($message->getContent()), $reporter, $message->getReference());

        $this->ticketStrategy->process($message);
    }

    private function getReporter($id)
    {
        return new User($id, User::TYPE_DIAMANTE);
    }

    private function getDiamanteUser()
    {
        return new DiamanteUser('test_email');
    }

    private function getDummyFrom()
    {
        return new Message\MessageSender(self::DUMMY_MESSAGE_FROM, 'Dummy User');
    }
}
