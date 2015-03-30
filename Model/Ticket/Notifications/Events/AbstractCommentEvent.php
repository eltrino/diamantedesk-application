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

use Diamante\DeskBundle\Model\Ticket\Notifications\ChangesProviderEvent;
use Diamante\DeskBundle\Model\Ticket\Notifications\CommentsEvent;

abstract class AbstractCommentEvent extends AbstractTicketEvent implements ChangesProviderEvent, CommentsEvent
{
    /**
     * @var string
     */
    private $commentContent;

    /**
     * @var bool
     */
    protected $private;

    public function __construct($id, $subject, $commentContent, $private)
    {
        $this->ticketId       = $id;
        $this->subject        = $subject;
        $this->commentContent = $commentContent;
        $this->private = $private;
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
     * @return bool
     */
    public function isPrivate()
    {
        return $this->private;
    }
}
