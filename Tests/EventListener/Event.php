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
namespace Diamante\DeskBundle\Tests\EventListener;

use Diamante\DeskBundle\Model\Ticket\Notifications\AttachmentsEvent;
use Diamante\DeskBundle\Model\Ticket\Notifications\ChangesProviderEvent;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationEvent;

class Event implements NotificationEvent, ChangesProviderEvent, AttachmentsEvent
{
    private $attachments;

    private $changes;

    private $aggregateId;

    private $eventName;

    private $headerText;

    private $subject;

    public function __construct(array $attachments, array $changes, $aggregateId, $eventName, $headerText, $subject)
    {
        $this->attachments = $attachments;
        $this->changes = $changes;
        $this->aggregateId = $aggregateId;
        $this->eventName = $eventName;
        $this->headerText = $headerText;
        $this->subject = $subject;
    }

    /**
     * @return array of attachments names
     */
    public function attachments()
    {
        return $this->attachments;
    }

    /**
     * Provide changes of entity of raised event
     * @param \ArrayAccess $changes
     * @return void
     */
    public function provideChanges(\ArrayAccess $changes)
    {
        foreach ($this->changes as $k => $v) {
            $changes[$k] = $v;
        }
    }

    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return $this->headerText;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
