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
namespace Eltrino\DiamanteDeskBundle\Form;

use Eltrino\DiamanteDeskBundle\Entity\Comment;
use Eltrino\DiamanteDeskBundle\Entity\Ticket;
use Eltrino\DiamanteDeskBundle\Form\Command\AssigneeTicketCommand;
use Eltrino\DiamanteDeskBundle\Form\Command\EditCommentCommand;
use Eltrino\DiamanteDeskBundle\Form\Command\CreateTicketCommand;
use Eltrino\DiamanteDeskBundle\Form\Command\UpdateTicketCommand;
use Eltrino\DiamanteDeskBundle\Form\Command\UpdateStatusCommand;

use Eltrino\DiamanteDeskBundle\Form\Command\AttachmentCommand;

use Eltrino\DiamanteDeskBundle\Entity\Branch;
use Eltrino\DiamanteDeskBundle\Form\Command\BranchCommand;
use Oro\Bundle\UserBundle\Entity\User;

class CommandFactory
{
    public function createCreateTicketCommand(Branch $branch = null, User $reporter = null)
    {
        $command = new CreateTicketCommand();
        if ($branch) {
            $command->branch = $branch;
        }
        if ($reporter) {
            $command->reporter = $reporter;
            $command->assignee = $reporter;
        }
        return $command;
    }

    public function createUpdateTicketCommand(Ticket $ticket)
    {
        $command = new UpdateTicketCommand();
        $command->id = $ticket->getId();
        $command->subject = $ticket->getSubject();
        $command->description = $ticket->getDescription();
        $command->reporter = $ticket->getReporter();
        $command->assignee = $ticket->getAssignee();
        $command->status = $ticket->getStatus();
        $command->priority = $ticket->getPriority();
        $command->branch = $ticket->getBranch();

        return $command;
    }

    public function createAssigneeTicketCommand(Ticket $ticket)
    {
        $command = new AssigneeTicketCommand();
        $command->id = $ticket->getId();
        $command->assignee = $ticket->getAssignee();

        return $command;
    }

    public function createAttachmentCommand(Ticket $ticket)
    {
        $command = new AttachmentCommand();
        $command->ticketId = $ticket->getId();

        return $command;
    }

    /**
     * Create comment command for create action
     * @param Ticket $ticket
     * @param User $author
     * @return EditCommentCommand
     */
    public function createEditCommentCommandForCreate(Ticket $ticket, User $author)
    {
        $command = new EditCommentCommand();
        $command->id = null;
        $command->content = null;
        $command->author = $author;
        $command->ticket = $ticket;
        $command->ticketStatus = $ticket->getStatus();

        return $command;
    }

    /**
     * Create Comment command for update action
     * @param Comment $comment
     * @return EditCommentCommand
     */
    public function createEditCommentCommandForUpdate(Comment $comment)
    {
        $command = new EditCommentCommand();
        $command->id = $comment->getId();
        $command->content = $comment->getContent();
        $command->author = $comment->getAuthor();
        $command->ticket = $comment->getTicket();
        $command->attachmentList = $comment->getAttachments();
        $command->ticketStatus = $comment->getTicket()->getStatus();

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
