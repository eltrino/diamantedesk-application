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

class CommentWasUpdated extends AbstractTicketEvent implements ChangesProviderEvent, AttachmentsEvent
{
    /**
     * @var string
     */
    private $commentContent;

    public function __construct($id, $subject, $commentContent)
    {
        $this->ticketId       = $id;
        $this->subject        = $subject;
        $this->commentContent = $commentContent;
    }

    /**
     * @return string
     */
    public function getEventName()
    {
        return 'commentWasUpdated';
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return 'Comment was updated';
    }

    /**
     * Provide changes of entity of raised event
     * @param \ArrayAccess $changes
     * @return void
     */
    public function provideChanges(\ArrayAccess $changes)
    {
        $changes['Comment'] = $this->commentContent;
    }

    /**
     * @return array of attachments names
     */
    public function attachments()
    {
        return array();
    }
}
