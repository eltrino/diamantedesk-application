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

use Diamante\ApiBundle\Annotation\ApiDoc;
use Diamante\ApiBundle\Routing\RestServiceInterface;
use Diamante\DeskBundle\Api\ApiPagingService;
use Diamante\DeskBundle\Api\Command;
use Diamante\DeskBundle\Api\Command\RemoveCommentAttachmentCommand;
use Diamante\DeskBundle\Api\Command\RetrieveCommentAttachmentCommand;
use Diamante\DeskBundle\Entity\Attachment;
use Diamante\DeskBundle\Entity\Comment;
use Diamante\DeskBundle\Model\Ticket\Filter\CommentFilterCriteriaProcessor;
use Diamante\UserBundle\Api\UserService;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Api\Dto\AttachmentInput;

class CommentApiServiceImpl extends CommentServiceImpl implements RestServiceInterface
{
    use ApiServiceImplTrait;

    /**
     * @var ApiPagingService
     */
    protected $apiPagingService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * Load Comment by given comment id
     *
     * @ApiDoc(
     *  description="Returns a comment",
     *  uri="/comments/{id}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see comment",
     *      404="Returned when the comment is not found"
     *  }
     * )
     *
     * @param int $id
     * @return \Diamante\DeskBundle\Model\Ticket\Comment
     */
    public function loadComment($id)
    {
        return parent::loadComment($id);
    }

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
        $this->isGranted('CREATE', 'Entity:DiamanteDeskBundle:Comment');

        \Assert\that($command->attachmentsInput)->nullOr()->all()
            ->isInstanceOf(AttachmentInput::class);

        /**
         * @var $ticket \Diamante\DeskBundle\Model\Ticket\Ticket
         */
        $ticket = $this->loadTicketBy($command->ticket);
        $author = User::fromString($command->author);
        $comment = $this->commentFactory->create($command->content, $ticket, $author, $command->private);

        $this->createAttachments($command, $comment);
        $ticket->updateTimestamps();
        $ticket->postNewComment($comment);
        $this->ticketRepository->store($ticket);

        return $comment;
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
     * @param $flush
     * @return array
     */
    public function addCommentAttachment(Command\AddCommentAttachmentCommand $command, $flush = false)
    {
        $this->prepareAttachmentInput($command);
        return parent::addCommentAttachment($command, true);
    }

    /**
     * Retrieves comment attachments
     *
     * @ApiDoc(
     *  description="Returns comment attachments",
     *  uri="/comments/{id}/attachments.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list comment attachments"
     *  }
     * )
     *
     * @param $id
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function listCommentAttachment($id)
    {
        return parent::listCommentAttachment($id);
    }

    /**
     * Retrieves Comment Attachment
     *
     * @ApiDoc(
     *  description="Returns a comment attachment",
     *  uri="/comments/{commentId}/attachments/{attachmentId}.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="commentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      },
     *      {
     *          "name"="attachmentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment attachment Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to see comment attachment",
     *      404="Returned when the attachment is not found"
     *  }
     * )
     *
     * @param RetrieveCommentAttachmentCommand $command
     * @return Attachment
     */
    public function getCommentAttachment(RetrieveCommentAttachmentCommand $command)
    {
        return parent::getCommentAttachment($command);
    }

    /**
     * Update certain properties of the Comment
     *
     * @ApiDoc(
     *  description="Update comment",
     *  uri="/comments/{id}.{_format}",
     *  method={
     *      "PUT",
     *      "PATCH"
     *  },
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      }
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to update comment",
     *      404="Returned when the comment is not found"
     *  }
     * )
     *
     * @param Command\UpdateCommentCommand $command
     * @param boolean $flush
     * @return Comment
     */
    public function updateCommentContentAndTicketStatus(Command\UpdateCommentCommand $command, $flush = false)
    {
        return parent::updateCommentContentAndTicketStatus($command, true);
    }

    /**
     * Delete Ticket Comment
     *
     * @ApiDoc(
     *  description="Delete comment",
     *  uri="/comments/{id}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete comment",
     *      404="Returned when the comment is not found"
     *  }
     * )
     *
     * @param int $id
     */
    public function deleteTicketComment($id)
    {
        parent::deleteTicketComment($id);
    }

    /**
     * Remove Attachment from Comment
     *
     * @ApiDoc(
     *  description="Remove comment attachment",
     *  uri="/comments/{commentId}/attachments/{attachmentId}.{_format}",
     *  method="DELETE",
     *  resource=true,
     *  requirements={
     *      {
     *          "name"="commentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment Id"
     *      },
     *      {
     *          "name"="attachmentId",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Comment attachment Id"
     *      }
     *  },
     *  statusCodes={
     *      204="Returned when successful",
     *      403="Returned when the user is not authorized to delete attachment",
     *      404="Returned when the comment or attachment is not found"
     *  }
     * )
     *
     * @param RemoveCommentAttachmentCommand $command
     * @return void
     * @throws \RuntimeException if Comment does not exists or Comment has no particular attachment
     */
    public function removeAttachmentFromComment(RemoveCommentAttachmentCommand $command)
    {
        parent::removeAttachmentFromComment($command, true);
    }

    /**
     * Retrieves list of all Comments.
     * Filters comments with parameters provided via GET request.
     * Time filtering parameters as well as paging/sorting configuration parameters can be found in \Diamante\DeskBundle\Api\Command\CommonFilterCommand class.
     * Time filtering values should be converted to UTC
     *
     * @ApiDoc(
     *  description="Returns all comments.",
     *  uri="/comments.{_format}",
     *  method="GET",
     *  resource=true,
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to list tickets"
     *  }
     * )
     *
     * @param Command\Filter\FilterCommentsCommand $command
     * @return Comment[]
     */
    public function listAllComments(Command\Filter\FilterCommentsCommand $command)
    {
        $criteriaProcessor = new CommentFilterCriteriaProcessor();
        $repository = $this->getCommentsRepository();
        $pagingProperties = $this->buildPagination($criteriaProcessor, $repository, $command, $this->apiPagingService, null);
        $criteria = $criteriaProcessor->getCriteria();

        $comments = $repository->filter($criteria, $pagingProperties);

        return $comments;
    }

    /**
     * @param ApiPagingService $apiPagingService
     */
    public function setApiPagingService(ApiPagingService $apiPagingService)
    {
        $this->apiPagingService = $apiPagingService;
    }


    /**
     * @param UserService $userService
     */
    public function setUserService(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Retrieves comment author data based on typed ID provided
     *
     * @ApiDoc(
     *  description="Returns person data",
     *  uri="/comment/{id}/author.{_format}",
     *  method="GET",
     *  resource=true,
     *  requirements={
     *       {
     *           "name"="id",
     *           "dataType"="integer",
     *           "requirement"="\d+",
     *           "description"="Author Id"
     *       }
     *   },
     *  statusCodes={
     *      200="Returned when successful",
     *      403="Returned when the user is not authorized to view tickets"
     *  }
     * )
     *
     * @param $id
     * @return array
     */
    public function getAuthorData($id)
    {
        $comment = $this->loadComment($id);

        $details = $this->userService->fetchUserDetails($comment->getAuthor());

        return $details;
    }
}
