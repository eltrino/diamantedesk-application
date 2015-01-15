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
namespace Diamante\DeskBundle\Api\Internal;

use Diamante\DeskBundle\Api\Command;

class CommentApiServiceImpl extends CommentServiceImpl
{
    use ApiServiceImplTrait;

    /**
     * Post Comment for Ticket
     *
     * @ApiDoc(
     *  description="Post comment",
     *  uri="/comments.{_format}",
     *  method="POST",
     *  resource=true,
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to post comment"
     *  }
     * )
     *
     * @param Command\CommentCommand $command
     * @return \Diamante\DeskBundle\Model\Ticket\Comment
     */
    public function postNewCommentForTicket(Command\CommentCommand $command)
    {
        $this->prepareAttachmentInput($command);
        return parent::postNewCommentForTicket($command);
    }

    /**
     * Add Attachments to Comment
     *
     * @ApiDoc(
     *  description="Add attachment to comment",
     *  uri="/comments/{commentId}/attachments.{_format}",
     *  method="POST",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="commentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      }
     *  },
     *  statusCodes={
     *      201="Returned when successful",
     *      403="Returned when the user is not authorized to add attachment to comment"
     *  }
     * )
     *
     * @param Command\AddCommentAttachmentCommand $command
     * @return void
     */
    public function addCommentAttachment(Command\AddCommentAttachmentCommand $command)
    {
        $this->prepareAttachmentInput($command);
        parent::addCommentAttachment($command);
    }
}
