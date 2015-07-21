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
namespace Diamante\DeskBundle\Tests\Model\Ticket\Notifications;

use Diamante\DeskBundle\Model\Ticket\Notifications\TicketNotification;

class NotificationTest extends \PHPUnit_Framework_TestCase
{
    use \Diamante\DeskBundle\Tests\EventListener\EventTrait;

    public function testCreate()
    {
        $changes = new \ArrayIterator();
        $changes['change_1'] = '1';
        $changes['change_2'] = '2';
        $attachments = array('file.jpg', 'doc.pdf');
        $notification = new TicketNotification(
            'unique_id', 'author@email.com', 'Header Text', 'Subject', $changes, $attachments, $this->event()
        );

        $this->assertEquals('unique_id', $notification->getTicketUniqueId());
        $this->assertEquals('author@email.com', $notification->getAuthor());
        $this->assertEquals('Header Text', $notification->getHeaderText());
        $this->assertEquals('Subject', $notification->getSubject());
        $this->assertEquals($changes, $notification->getChangeList());
        $this->assertEquals($attachments, $notification->getAttachments());
    }
}
