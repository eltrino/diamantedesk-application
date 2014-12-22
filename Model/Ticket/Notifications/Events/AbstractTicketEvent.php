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

use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationEvent;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractTicketEvent extends Event implements NotificationEvent
{
    /**
     * @var string
     */
    protected $ticketId;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $recipientsList;

    /**
     * @var array
     */
    protected $recipientUserIds = array();

    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->ticketId;
    }

    /**
     * @return array
     */
    public function getRecipientUserIds()
    {
        return $this->recipientUserIds;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
