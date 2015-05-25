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

class NotificationDeliveryManager
{
    /**
     * @var \SplQueue
     */
    private $queue;

    private static $managerInstance;

    public function __construct(\SplQueue $queue = null)
    {
        if (is_null($queue)) {
            $queue = new \SplQueue();
        }
        $this->queue = $queue;
    }

    /**
     * Deliver notifications via given instance of Notifier
     * @param Notifier $notifier
     */
    public function deliver(Notifier $notifier)
    {
        foreach ($this->queue as $notification) {
            $notifier->notify($notification);
            $this->queue->dequeue();
        }
    }

    /**
     * @param Notification $notification
     */
    public function add(Notification $notification)
    {
        $this->queue->push($notification);
    }

    /**
     * @return NotificationDeliveryManager
     */
    public static function initialize()
    {
        if (is_null(self::$managerInstance)) {
            self::$managerInstance = new self();
        }
        return self::$managerInstance;
    }
}
