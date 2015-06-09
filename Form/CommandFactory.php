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
namespace Diamante\DeskBundle\Form;

use Diamante\DeskBundle\Api\Command\AddTicketAttachmentCommand;
use Diamante\DeskBundle\Entity\Comment;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Diamante\DeskBundle\Api\Command\AssigneeTicketCommand;
use Diamante\DeskBundle\Api\Command\CommentCommand;
use Diamante\DeskBundle\Api\Command\UpdateTicketCommand;
use Diamante\DeskBundle\Api\Command\UpdateStatusCommand;

use Diamante\DeskBundle\Api\Command\MoveTicketCommand;

use Diamante\DeskBundle\Entity\Branch;
use Diamante\DeskBundle\Api\Command\CreateTicketCommand;
use Diamante\UserBundle\Model\User;
use Diamante\DeskBundle\Api\Command\AddWatcherCommand;

class CommandFactory
{
    /**
     * @param Branch $branch
     * @param User   $reporter
     *
     * @return CreateTicketCommand
     */
    public function createCreateTicketCommand(Branch $branch = null, User $reporter = null)
    {
        $command = new CreateTicketCommand();
        if ($branch) {
            $command->branch = $branch;
            if ($branch->getDefaultAssignee()) {
                $command->assignee = $branch->getDefaultAssignee();
            }
        }
        if ($reporter) {
            $command->reporter = $reporter;
        }
        return $command;
    }

    /**
     * @param Ticket $ticket
     *
     * @return UpdateTicketCommand
     */
    public function createUpdateTicketCommand(Ticket $ticket)
    {
        $command = new UpdateTicketCommand();
        $command->id = $ticket->getId();
        $command->key = (string) $ticket->getKey();
        $command->subject = $ticket->getSubject();
        $command->description = $ticket->getDescription();
        $command->reporter = $ticket->getReporter();
        $command->assignee = $ticket->getAssignee();
        $command->status = $ticket->getStatus();
        $command->priority = $ticket->getPriority();
        $command->branch = $ticket->getBranch();
        $command->source = $ticket->getSource();
        $command->tags = $ticket->getTags();

        return $command;
    }

    /**
     * @param Ticket $ticket
     *
     * @return AssigneeTicketCommand
     */
    public function createAssigneeTicketCommand(Ticket $ticket)
    {
        $command = new AssigneeTicketCommand();
        $command->id = $ticket->getId();
        $command->assignee = $ticket->getAssignee();

        return $command;
    }

    /**
     * @param Ticket $ticket
     *
     * @return MoveTicketCommand
     */
    public function createMoveTicketCommand(Ticket $ticket)
    {
        $command = new MoveTicketCommand();
        $command->id = $ticket->getId();
        $command->branch = $ticket->getBranch();

        return $command;
    }

    /**
     * @param Ticket $ticket
     *
     * @return AddWatcherCommand
     */
    public function addWatcherCommand(Ticket $ticket)
    {
        $command = new AddWatcherCommand();
        $command->id = $ticket->getId();

        return $command;
    }

    /**
     * @param Ticket $ticket
     *
     * @return AddTicketAttachmentCommand
     */
    public function createAddTicketAttachmentCommand(Ticket $ticket)
    {
        $command = new AddTicketAttachmentCommand();
        $command->ticketId = $ticket->getId();

        return $command;
    }

    /**
     * Create comment command for create action
     * @param Ticket $ticket
     * @param User $author
     * @return CommentCommand
     */
    public function createCommentCommandForCreate(Ticket $ticket, User $author)
    {
        $command = new CommentCommand();
        $command->id = null;
        $command->content = null;
        $command->ticket = $ticket->getId();
        $command->author = (string)$author;
        $command->ticketStatus = $ticket->getStatus();

        return $command;
    }

    /**
     * Create Comment command for update action
     * @param Comment $comment
     * @return CommentCommand
     */
    public function createCommentCommandForUpdate(Comment $comment)
    {
        $command = new CommentCommand();
        $command->id = $comment->getId();
        $command->content = $comment->getContent();
        $command->author = (string)$comment->getAuthor();
        $command->ticket = $comment->getTicket()->getId();
        $command->attachmentList = $comment->getAttachments();
        $command->ticketStatus = $comment->getTicket()->getStatus();
        $command->private = $comment->isPrivate();

        return $command;
    }

    /**
     * @param Ticket $ticket
     * @return UpdateStatusCommand
     */
    public function createUpdateStatusCommandForView(Ticket $ticket)
    {
        $command           = new UpdateStatusCommand();
        $command->ticketId = $ticket->getId();
        $command->status   = $ticket->getStatus();

        return $command;
    }
}
