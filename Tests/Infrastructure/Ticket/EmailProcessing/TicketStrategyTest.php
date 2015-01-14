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

use Diamante\DeskBundle\Model\User\User;
use Diamante\EmailProcessingBundle\Model\Message;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Infrastructure\Ticket\EmailProcessing\TicketStrategy;
use Diamante\ApiBundle\Model\ApiUser\ApiUser;

class TicketStrategyTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_BRANCH_ID  = 'default_branch_id';
    const DUMMY_BRANCH_ID    = 'dummy_branch_id';

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
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUserRepository
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUserRepository
     */
    private $apiUserRepository;

    /**
     * @var \Diamante\ApiBundle\Model\ApiUser\ApiUserFactory
     * @Mock \Diamante\ApiBundle\Model\ApiUser\ApiUserFactory
     */
    private $apiUserFactory;

    /**
     * @var \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Diamante\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $emailProcessingSettings;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->ticketStrategy = new TicketStrategy(
            $this->messageReferenceService,
            $this->branchEmailConfigurationService,
            $this->apiUserRepository,
            $this->apiUserFactory,
            $this->emailProcessingSettings
        );
    }

    public function testProcessWhenApiUserExists()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $apiUser = $this->getApiUser();

        $this->apiUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($apiUser));


        $reporter = $this->getReporter($apiUser->getId());

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

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenApiUserNotExists()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;

        $this->apiUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue(null));

        $apiUser = $this->getApiUser();

        $this->apiUserFactory->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM),
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($apiUser));

        $this->apiUserRepository->expects($this->once())
            ->method('store')
            ->with(
                $this->equalTo($apiUser)
            );

        $reporter = $this->getReporter($apiUser->getId());

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

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithoutReferenceWithDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO);

        $assigneeId = 1;
        $apiUser = $this->getApiUser();

        $this->apiUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($apiUser));


        $reporter = $this->getReporter($apiUser->getId());

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

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithoutReferenceWithoutDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO);

        $reporterId = 1;
        $assigneeId = 1;
        $apiUser = $this->getApiUser();

        $this->apiUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($apiUser));


        $reporter = $this->getReporter($apiUser->getId());

        preg_match('/@(.*)/', self::DUMMY_MESSAGE_FROM, $output);
        $customerDomain = $output[1];

        $this->branchEmailConfigurationService->expects($this->once())
            ->method('getConfigurationBySupportAddressAndCustomerDomain')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_TO),
                $this->equalTo($customerDomain)
            )->will($this->returnValue(self::DUMMY_BRANCH_ID));

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($message->getMessageId()), self::DUMMY_BRANCH_ID, $message->getSubject(), $message->getContent(),
                $reporter, $assigneeId);

        $this->ticketStrategy->process($message);
    }


    public function testProcessWhenMessageWithReference()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO, self::DUMMY_REFERENCE);

        $apiUser = $this->getApiUser();

        $this->apiUserRepository->expects($this->once())
            ->method('findUserByEmail')
            ->with(
                $this->equalTo(self::DUMMY_MESSAGE_FROM)
            )->will($this->returnValue($apiUser));


        $reporter = $this->getReporter($apiUser->getId());

        $this->messageReferenceService->expects($this->once())
            ->method('createCommentForTicket')
            ->with($this->equalTo($message->getContent()), $reporter, $message->getReference());

        $this->ticketStrategy->process($message);
    }

    private function getReporter($id)
    {
        return new User($id, User::TYPE_DIAMANTE);
    }

    private function getApiUser()
    {
        return new ApiUser('test_email', 'test_username', 'salt',  array());
    }
}
