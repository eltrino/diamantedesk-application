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
namespace Diamante\DeskBundle\Model\Ticket\Notifications;

use Diamante\DeskBundle\Model\Shared\Notification;

class TicketNotification implements Notification
{
    /**
     * @var string
     */
    private $ticketUniqueId;

    /**
     * @var string
     */
    private $headerText;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var \Diamante\UserBundle\Model\User
     */
    private $author;

    /**
     * @var \ArrayAccess
     */
    private $changeList;

    /**
     * @var array
     */
    private $attachments;

    public function __construct(
        $ticketUniqueId, $author, $headerText, $subject, \ArrayAccess $changeList, $attachments = array()
    ) {
        $this->ticketUniqueId = $ticketUniqueId;
        $this->author = $author;
        $this->headerText = $headerText;
        $this->subject = $subject;
        $this->changeList = $changeList;
        $this->attachments = $attachments;
    }

    /**
     * @return string
     */
    public function getTicketUniqueId()
    {
        return $this->ticketUniqueId;
    }

    /**
     * @return int
     */
    public function getAuthor()
    {
        return $this->author;
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

    /**
     * @return \ArrayAccess
     */
    public function getChangeList()
    {
        return $this->changeList;
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}
