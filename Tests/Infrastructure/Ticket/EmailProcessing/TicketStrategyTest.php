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

namespace Eltrino\DiamanteDeskBundle\Tests\Infrastructure\Ticket\EmailProcessing;

use Eltrino\EmailProcessingBundle\Model\Message;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Eltrino\DiamanteDeskBundle\Infrastructure\Ticket\EmailProcessing\TicketStrategy;

class TicketStrategyTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_UNIQUE_ID  = 'dummy_unique_id';
    const DUMMY_MESSAGE_ID = 'dummy_message_id';
    const DUMMY_SUBJECT    = 'dummy_subject';
    const DUMMY_CONTENT    = 'dummy_content';
    const DUMMY_REFERENCE  = 'dummy_reference';

    /**
     * @var TicketStrategy
     */
    private $ticketStrategy;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl
     * @Mock \Eltrino\DiamanteDeskBundle\Model\Ticket\EmailProcessing\Services\MessageReferenceServiceImpl
     */
    private $messageReferenceService;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->ticketStrategy = new TicketStrategy($this->messageReferenceService);
    }

    public function testProcessWhenMessageWithoutReference()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT);

        $branchId   = 1;
        $reporterId = 1;
        $assigneeId = 1;

        $this->messageReferenceService->expects($this->once())
            ->method('createTicket')
            ->with($this->equalTo($message->getMessageId()), $branchId, $message->getSubject(), $message->getContent(),
                $reporterId, $assigneeId);

        $this->ticketStrategy->process($message);
    }

    public function testProcessWhenMessageWithReference()
    {
        $message = new Message(self::DUMMY_UNIQUE_ID, self::DUMMY_MESSAGE_ID, self::DUMMY_SUBJECT,
            self::DUMMY_CONTENT, self::DUMMY_REFERENCE);

        $reporterId = 1;

        $this->messageReferenceService->expects($this->once())
            ->method('createCommentForTicket')
            ->with($this->equalTo($message->getContent()), $reporterId, $message->getReference());

        $this->ticketStrategy->process($message);
    }
}
