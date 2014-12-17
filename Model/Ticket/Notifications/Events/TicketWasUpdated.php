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

use Diamante\DeskBundle\Model\Ticket\Notifications\AttachmentsEvent;
use Diamante\DeskBundle\Model\Ticket\Notifications\ChangesProviderEvent;
class TicketWasUpdated extends AbstractTicketEvent implements ChangesProviderEvent, AttachmentsEvent
{
    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $reporterFullName;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $source;

    /**
     * @var array
     */
    private $attachments;

    public function __construct($id, $subject, $description, $reporterFullName, $priority, $status, $source)
    {
        $this->ticketId          = $id;
        $this->subject           = $subject;
        $this->description       = $description;
        $this->reporterFullName  = $reporterFullName;
        $this->priority          = $priority;
        $this->status            = $status;
        $this->source            = $source;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return 'ticketWasUpdated';
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return 'Ticket was updated';
    }

    /**
     * Provide changes of entity of raised event
     * @param \ArrayAccess $changes
     * @return void
     */
    public function provideChanges(\ArrayAccess $changes)
    {
        $changes['Subject']     = $this->subject;
        $changes['Description'] = $this->description;
        $changes['Reporter']    = $this->reporterFullName;
        $changes['Priority']    = $this->priority;
        $changes['Status']      = $this->status;
        $changes['Source']      = $this->source;
    }

    /**
     * @return array of attachments names
     */
    public function attachments()
    {
        return $this->attachments;
    }

    /**
     * @param string $attachment
     * @return void
     */
    public function pushAttachment($attachment)
    {
        if (false === is_string($attachment)) {
            throw new \InvalidArgumentException('Wrong format of attachment name.');
        }
        $this->attachments[] = $attachment;
    }
}
