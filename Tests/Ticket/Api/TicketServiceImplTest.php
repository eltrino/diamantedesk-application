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

use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\AttachmentInput;
use Eltrino\DiamanteDeskBundle\Attachment\Model\File;
use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\DiamanteDeskBundle\Ticket\Api\TicketServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class TicketServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID     = 1;
    const DUMMY_ATTACHMENT_ID = 1;
    const DUMMY_TICKET_SUBJECT      = 'Subject';
    const DUMMY_TICKET_DESCRIPTION  = 'Description';
    const DUMMY_FILENAME      = 'dummy_filename.ext';
    const DUMMY_FILE_CONTENT  = 'DUMMY_CONTENT';
    const DUMMY_STATUS        = 'dummy';

    /**
     * @var TicketServiceImpl
     */
    private $ticketService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Model\TicketRepository
     */
    private $ticketRepository;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\AttachmentService
     */
    private $ticketAttachmentService;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @Mock \Eltrino\DiamanteDeskBundle\Entity\Ticket
     */
    private $ticket;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
     * @Mock \Eltrino\DiamanteDeskBundle\Branch\Model\BranchRepository
     */
    private $branchRepository;

    /**
     * @var\ Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\TicketFactory
     * @Mock Eltrino\DiamanteDeskBundle\Ticket\Api\Factory\TicketFactory
     */
    private $ticketFactory;

    /**
     * @var \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService
     * @Mock \Eltrino\DiamanteDeskBundle\Ticket\Api\Internal\UserService
     */
    private $userService;

    protected function setUp()
    {
        MockAnnotations::init($this);

        $this->ticketService = new TicketServiceImpl(
            $this->ticketRepository,
            $this->branchRepository,
            $this->ticketFactory,
            $this->ticketAttachmentService,
            $this->userService
        );
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatAttachmentRetrievingThrowsExceptionWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $this->ticketService->getTicketAttachment(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Ticket has no such attachment.
     */
    public function thatAttachmentRetrievingThrowsExceptionWhenTicketHasNoAttachment()
    {
        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Status::CLOSED
        );
        $ticket->addAttachment($this->attachment());
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->ticketService->getTicketAttachment(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     */
    public function thatTicketAttachmentRetrieves()
    {
        $attachment = $this->attachment();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('getAttachment')->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));
        $this->ticketService->getTicketAttachment(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatAttachmentsAddingThrowsExceptionWhenTicketNotExists()
    {
        $this->ticketService->addAttachmentsForTicket($this->attachmentInputs(), self::DUMMY_TICKET_ID);
    }

    /**
     * @test
     */
    public function thatAttachmentsAddsForTicket()
    {
        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Status::CLOSED
        );
        $attachmentInputs = $this->attachmentInputs();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));
        $this->ticketAttachmentService->expects($this->once())->method('createAttachmentsForItHolder')
            ->with($this->equalTo($attachmentInputs), $this->equalTo($ticket));
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->ticketService->addAttachmentsForTicket($attachmentInputs, self::DUMMY_TICKET_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $this->ticketService->removeAttachmentFromTicket(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Attachment loading failed. Ticket has no such attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenTicketHasNoAttachment()
    {
        $ticket = new Ticket(
            self::DUMMY_TICKET_SUBJECT,
            self::DUMMY_TICKET_DESCRIPTION,
            $this->createBranch(),
            $this->createReporter(),
            $this->createAssignee(),
            Status::CLOSED
        );
        $ticket->addAttachment($this->attachment());
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->ticketService->removeAttachmentFromTicket(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     */
    public function thatAttachmentRemovesFromTicket()
    {
        $attachment = $this->attachment();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('getAttachment')->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));

        $this->ticket->expects($this->once())->method('removeAttachment')->with($this->equalTo($attachment));

        $this->ticketAttachmentService->expects($this->once())->method('removeAttachmentFromItHolder')
            ->with($this->equalTo($attachment));

        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));

        $this->ticketService->removeAttachmentFromTicket(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket loading failed, ticket not found.
     */
    public function testUpdateStatusWhenTicketDoesNotExists()
    {
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue(null));

        $this->ticketService->updateStatus(self::DUMMY_TICKET_ID, self::DUMMY_STATUS);
    }

    /**
     * @test
     */
    public function testUpdateStatus()
    {
        $status = STATUS::NEW_ONE;

        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('updateStatus')->with($status);
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($this->ticket));
        $this->ticketService->updateStatus(self::DUMMY_TICKET_ID, $status);
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
     * @return Attachment
     */
    private function attachment()
    {
        return new Attachment(new File('filename.ext'));
    }

    /**
     * @return AttachmentInput
     */
    private function attachmentInputs()
    {
        $attachmentInput = new AttachmentInput();
        $attachmentInput->setFilename(self::DUMMY_FILENAME);
        $attachmentInput->setContent(self::DUMMY_FILE_CONTENT);
        return array($attachmentInput);
    }
}
