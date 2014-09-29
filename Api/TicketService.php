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

namespace Eltrino\DiamanteDeskBundle\Api;

use Eltrino\DiamanteDeskBundle\Api\Command\AssigneeTicketCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\CreateTicketCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\UpdateStatusCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\UpdateTicketCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Eltrino\DiamanteDeskBundle\Api\Command\RetrieveTicketAttachmentCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\AddTicketAttachmentCommand;
use Eltrino\DiamanteDeskBundle\Api\Command\RemoveTicketAttachmentCommand;

interface TicketService
{
    /**
     * Load Ticket by given ticket id
     * @param int $ticketId
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     */
    public function loadTicket($ticketId);

    /**
     * Retrieves Ticket Attachment
     * @param RetrieveTicketAttachmentCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Entity\Attachment
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function getTicketAttachment(RetrieveTicketAttachmentCommand $command);

    /**
     * Adds Attachments for Ticket
     * @param AddTicketAttachmentCommand $command
     * @return void
     */
    public function addAttachmentsForTicket(AddTicketAttachmentCommand $command);

    /**
     * Remove Attachment from Ticket
     * @param RemoveTicketAttachmentCommand $command
     * @return void
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function removeAttachmentFromTicket(RemoveTicketAttachmentCommand $command);

    /**
     * Create Ticket
     * @param CreateTicketCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket(CreateTicketCommand $command);

    /**
     * @param UpdateTicketCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket(UpdateTicketCommand $command);

    /**
     * @param UpdateStatusCommand $command
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function updateStatus(UpdateStatusCommand $command);

    /**
     * Delete Ticket
     * @param $ticketId
     * @return null
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($ticketId);

    /**
     * Assign Ticket to specified User
     * @param AssigneeTicketCommand $command
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket(AssigneeTicketCommand $command);
}
