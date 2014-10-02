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

use Diamante\EmailProcessingBundle\Model\Message;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Diamante\DeskBundle\Infrastructure\Ticket\EmailProcessing\TicketStrategy;
use Diamante\DeskBundle\Api\Command\CreateTicketFromMessageCommand;
use Diamante\DeskBundle\Api\Command\CreateCommentFromMessageCommand;

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
     * @var \Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl
     * @Mock \Diamante\DeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl
     */
    private $messageReferenceService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Api\EmailProcessing\BranchEmailConfigurationService
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Api\EmailProcessing\BranchEmailConfigurationService
     */
    private $branchEmailConfigurationService;

    /**
     * @var \Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings
     * @Mock \Eltrino\EmailProcessingBundle\Model\Mail\SystemSettings
     */
    private $emailProcessingSettings;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->ticketStrategy = new TicketStrategy(
            $this->messageReferenceService,
            $this->branchEmailConfigurationService,
            $this->emailProcessingSettings
        );
    }

    public function testProcessWhenMessageWithoutReferenceWithDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO);

        $reporterId = 1;
        $assigneeId = 1;

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

        $createTicketFromMessageCommand = new CreateTicketFromMessageCommand();
        $createTicketFromMessageCommand->messageId   = $message->getMessageId();
        $createTicketFromMessageCommand->branchId    = $branchId;
        $createTicketFromMessageCommand->subject     = $message->getSubject();
        $createTicketFromMessageCommand->description = $message->getContent();
        $createTicketFromMessageCommand->reporterId  = $reporterId;
        $createTicketFromMessageCommand->assigneeId  = $assigneeId;

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($createTicketFromMessageCommand));

        $this->ticketStrategy->process($message);
    }


    public function testProcessWhenMessageWithoutReferenceWithoutDefaultBranch()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO);

        $reporterId = 1;
        $assigneeId = 1;

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
                $reporterId, $assigneeId);

        $this->ticketStrategy->process($message);
    }


    public function testProcessWhenMessageWithReference()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_MESSAGE_FROM, self::DUMMY_MESSAGE_TO, self::DUMMY_REFERENCE);

        $reporterId = 1;

        $createCommentFromMessageCommand = new CreateCommentFromMessageCommand();
        $createCommentFromMessageCommand->authorId  = $reporterId;
        $createCommentFromMessageCommand->content   = $message->getContent();
        $createCommentFromMessageCommand->messageId = $message->getReference();

        $this->messageReferenceService->expects($this->once())
            ->method('createCommentForTicket')
            ->with($this->equalTo($createCommentFromMessageCommand));

        $this->ticketStrategy->process($message);
    }
}
