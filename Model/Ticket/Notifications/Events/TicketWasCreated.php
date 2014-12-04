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
namespace Diamante\DeskBundle\Model\Ticket\Notifications\Events;

use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\Source;

class TicketWasCreated extends AbstractTicketEvent
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $reporterEmail;

    /**
     * @var Priority
     */
    private $priority;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var string
     */
    private $branchName;

    /**
     * @var string
     */
    private $assigneeEmail;

    public function __construct($id, $branchName, $subject, $description, $reporterEmail, $assigneeEmail,
                                Priority $priority, Status $status, Source $source, $recipientsList)
    {

        $this->ticketId       = $id;
        $this->branchName     = $branchName;
        $this->subject        = $subject;
        $this->description    = $description;
        $this->reporterEmail  = $reporterEmail;
        $this->assigneeEmail  = $assigneeEmail;
        $this->priority       = $priority;
        $this->status         = $status;
        $this->source         = $source;
        $this->recipientsList = $recipientsList;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return 'ticketWasCreated';
    }

    /**
     * @return string
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getReporterEmail()
    {
        return $this->reporterEmail;
    }

    /**
     * @return string
     */
    public function getAssigneeEmail()
    {
        return $this->assigneeEmail;
    }

    /**
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return Priority
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
