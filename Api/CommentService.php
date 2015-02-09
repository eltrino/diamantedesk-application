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

namespace Diamante\DeskBundle\Api;

use Diamante\DeskBundle\Api\Command\AddCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\CommentCommand;
use Diamante\DeskBundle\Api\Command\RetrieveCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;
use Diamante\DeskBundle\Model\Attachment\Attachment;

/**
 * Interface CommentService
 * @package Diamante\DeskBundle\Api
 * @codeCoverageIgnore
 */
interface CommentService
{
    /**
     * Load Comment by given comment id
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Comment
     */
    public function loadComment($id);

    /**
     * Post Comment for Ticket
     * @param CommentCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Comment
     */
    public function postNewCommentForTicket(CommentCommand $command);

    /**
     * Retrieves Comment Attachment
     * @param RetrieveCommentAttachmentCommand $command
     * @return Attachment
     */
    public function getCommentAttachment(RetrieveCommentAttachmentCommand $command);

    /**
     * Add Attachments to Comment
     * @param AddCommentAttachmentCommand $command
     * @return array
     */
    public function addCommentAttachment(AddCommentAttachmentCommand $command);

    /**
     * Update Ticket Comment content
     * @param CommentCommand $command
     * @return void
     */
    public function updateTicketComment(CommentCommand $command);

    /**
     * Update certain properties of the Comment
     * @param Command\UpdateCommentCommand $command
     * @return void
     */
    public function updateCommentContentAndTicketStatus(Command\UpdateCommentCommand $command);

    /**
     * Delete Ticket Comment
     * @param integer $commentId
     */
    public function deleteTicketComment($commentId);

    /**
     * Remove Attachment from Comment
     * @param RemoveCommentAttachmentCommand $command
     * @return void
     * @throws \RuntimeException if Comment does not exists or Comment has no particular attachment
     */
    public function removeAttachmentFromComment(RemoveCommentAttachmentCommand $command);
}
