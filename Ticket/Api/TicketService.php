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

namespace Eltrino\DiamanteDeskBundle\Ticket\Api;

use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface TicketService
{
    /**
     * Retrieves Ticket Attachment
     * @param $ticketId
     * @param $attachmentId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Attachment
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function getTicketAttachment($ticketId, $attachmentId);

    /**
     * Adds Attachments for Ticket
     * @param array $attachmentsInput array of AttachmentInput DTOs
     * @param $ticketId
     * @return void
     */
    public function addAttachmentsForTicket(array $attachmentsInput, $ticketId);

    /**
     * Remove Attachment from Ticket
     * @param $ticketId
     * @param $attachmentId
     * @return void
     * @throws \RuntimeException if Ticket does not exists or Ticket has no particular attachment
     */
    public function removeAttachmentFromTicket($ticketId, $attachmentId);

    /**
     * Create Ticket
     * @param $branchId
     * @param $subject
     * @param $description
     * @param $reporterId
     * @param $assigneeId
     * @param $priority
     * @param $status
     * @param array $attachmentInputs
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required branch, reporter, assignee
     */
    public function createTicket($branchId, $subject, $description, $reporterId, $assigneeId, $priority, $status = null, array $attachmentInputs = null);

    /**
     * @param $ticketId
     * @param $subject
     * @param $description
     * @param $reporterId
     * @param $assigneeId
     * @param $priority
     * @param $status
     * @param array $attachmentInputs
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket and assignee
     */
    public function updateTicket($ticketId, $subject, $description, $reporterId, $assigneeId, $priority, $status, array $attachmentInputs = null);

    /**
     * @param $ticketId
     * @param $status
     * @return \Eltrino\DiamanteDeskBundle\Ticket\Model\Ticket
     * @throws \RuntimeException if unable to load required ticket
     */
    public function updateStatus($ticketId, $status);

    /**
     * Delete Ticket
     * @param $ticketId
     * @return null
     * @throws \RuntimeException if unable to load required ticket
     */
    public function deleteTicket($ticketId);

    /**
     * Assign Ticket to specified User
     * @param $ticketId
     * @param $assigneeId
     * @return \Eltrino\DiamanteDeskBundle\Entity\Ticket
     * @throws \RuntimeException if unable to load required ticket, assignee
     */
    public function assignTicket($ticketId, $assigneeId);
}
