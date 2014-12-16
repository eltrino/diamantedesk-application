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

use Diamante\DeskBundle\Model\Ticket\Notifications\Notification;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;

class NotificationDeliveryManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\Notifier
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\Notifier
     */
    private $notifier;

    protected function setUp()
    {
        MockAnnotations::init($this);
    }

    public function testDeliver()
    {
        $notification = new Notification(
            'unique_id', 1, 'Header', 'Subject', new \ArrayIterator(array('key' => 'value')), array('file.ext')
        );

        $manager = new NotificationDeliveryManager();

        $manager->add($notification);

        $this->notifier->expects($this->once())->method('notify')->with($notification);

        $manager->deliver($this->notifier);
    }
}
