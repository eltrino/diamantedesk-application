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

use Eltrino\DiamanteDeskBundle\Entity\Comment;
use Eltrino\DiamanteDeskBundle\Form\Command\EditCommentCommand;

interface CommentService
{
    /**
     * Post Comment for Ticket
     * @param string $content
     * @param integer $ticketId
     * @param integer $authorId
     * @param \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto
     * @return void
     */
    public function postNewCommentForTicket($content, $ticketId, $authorId, \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto = null);

    public function getCommentAttachment($commentId, $attachmentId);

    /**
     * Update Ticket Comment content
     * @param integer $commentId
     * @param string $content
     * @param \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto
     */
    public function updateTicketComment($commentId, $content, \Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FilesListDto $filesListDto = null);

    /**
     * Delete Ticket Comment
     * @param integer $commentId
     */
    public function deleteTicketComment($commentId);

    /**
     * Remove Attachment from Comment
     * @param $commentId
     * @param $attachmentId
     * @return void
     * @throws \RuntimeException if Comment does not exists or Comment has no particular attachment
     */
    public function removeAttachmentFromComment($commentId, $attachmentId);
}
