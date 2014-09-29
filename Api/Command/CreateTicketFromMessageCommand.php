<?php

namespace Diamante\DeskBundle\Api\Command;

class CreateTicketFromMessageCommand
{
    public $messageId;

    public $branchId;

    public $subject;

    public $description;

    public $reporterId;

    public $assigneeId;

    public $priority;

    public $status;

    public $attachments;
}
