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

use Eltrino\DiamanteDeskBundle\Model\Ticket\Comment;
use Eltrino\DiamanteDeskBundle\Api\Command\EditCommentCommand;

interface CommentService
{
    /**
     * Load Comment by given comment id
     * @param int $commentId
     * @return \Eltrino\DiamanteDeskBundle\Model\Ticket\Comment
     */
    public function loadComment($commentId);

    /**
     * Post Comment for Ticket
     * @param EditCommentCommand $command
     * @return void
     */
    public function postNewCommentForTicket(EditCommentCommand $command);

    /**
     * Retrieves Comment Attachment
     * @param integer $commentId
     * @param integer $attachmentId
     * @return Attachment
     */
    public function getCommentAttachment($commentId, $attachmentId);

    /**
     * Update Ticket Comment content
     * @param EditCommentCommand $command
     * @return void
     */
    public function updateTicketComment(EditCommentCommand $command);

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
