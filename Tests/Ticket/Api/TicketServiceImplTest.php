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

use Eltrino\DiamanteDeskBundle\Entity\Attachment;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Ticket\Model\Status;
use Eltrino\DiamanteDeskBundle\Ticket\Api\TicketServiceImpl;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class TicketServiceImplTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_TICKET_ID     = 1;
    const DUMMY_ATTACHMENT_ID = 1;
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
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * @Mock \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $uploadedFile;

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
     * @expectedExceptionMessage Ticket not found.
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
     * @expectedExceptionMessage Ticket has no such attachment.
     */
    public function thatAttachmentRetrievingThrowsExceptionWhenTicketHasNoAttachment()
    {
        $ticket = new Ticket();
        $ticket->addAttachment(new Attachment('filename.ext'));
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->ticketService->getTicketAttachment(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     */
    public function thatTicketAttachmentRetrieves()
    {
        $attachment = new Attachment('filename.ext');
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($this->ticket));

        $this->ticket->expects($this->once())->method('getAttachment')->with($this->equalTo(self::DUMMY_ATTACHMENT_ID))
            ->will($this->returnValue($attachment));
        $this->ticketService->getTicketAttachment(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket not found.
     */
    public function thatAttachmentAddingThrowsExceptionWhenTicketNotExists()
    {
        $this->ticketService->addAttachmentForTicket($this->uploadedFile, self::DUMMY_TICKET_ID);
    }

    /**
     * @test
     */
    public function thatAttachmentAddsForTicket()
    {
        $ticket = new Ticket();
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));
        $this->ticketAttachmentService->expects($this->once())->method('createAttachmentForItHolder')
            ->with($this->equalTo($this->uploadedFile), $this->equalTo($ticket));
        $this->ticketRepository->expects($this->once())->method('store')->with($this->equalTo($ticket));

        $this->ticketService->addAttachmentForTicket($this->uploadedFile, self::DUMMY_TICKET_ID);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Ticket not found.
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
     * @expectedExceptionMessage Ticket has no such attachment.
     */
    public function thatAttachmentRemovingThrowsExceptionWhenTicketHasNoAttachment()
    {
        $ticket = new Ticket();
        $ticket->addAttachment(new Attachment('filename.ext'));
        $this->ticketRepository->expects($this->once())->method('get')->with($this->equalTo(self::DUMMY_TICKET_ID))
            ->will($this->returnValue($ticket));

        $this->ticketService->removeAttachmentFromTicket(self::DUMMY_TICKET_ID, self::DUMMY_ATTACHMENT_ID);
    }

    /**
     * @test
     */
    public function thatAttachmentRemovesFromTicket()
    {
        $attachment = new Attachment('filename.ext');
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
     * @expectedExceptionMessage Ticket not found.
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
}
