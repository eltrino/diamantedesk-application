<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\UserBundle\EventListener;


use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Diamante\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserNotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotificationManager
     */
    protected $manager;

    public function __construct(NotificationManager $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            'user.notification' => ['onUserNotification', 0]
        ];
    }

    public function onUserNotification(UserEvent $event)
    {
        $this->manager->notifyByScenario(
            $event->getScenario(),
            $event->getUser(),
            ['activation_hash' => $event->getUser()->getApiUser()->getHash()]
        );
    }
}