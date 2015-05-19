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
namespace Diamante\UserBundle\EventListener;

use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager;
use Diamante\UserBundle\Model\ApiUser\Notifications\UserNotification;
use Diamante\DeskBundle\Model\Shared\DomainEvent;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApiUserNotificationsSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    /**
     * @var NotificationDeliveryManager
     */
    private $manager;

    /**
     * @var ConfigManager
     */
    private $configManager;

    private static $events = array(
        'apiUserPasswordWasChanged' => 'processEvent'
    );

    public function __construct(
        SecurityFacade $securityFacade,
        NotificationDeliveryManager $manager,
        ConfigManager $configManager
    ) {
        $this->securityFacade = $securityFacade;
        $this->manager = $manager;
        $this->configManager = $configManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return self::$events;
    }

    /**
     * @param DomainEvent $event
     * @return void
     */
    public function processEvent(DomainEvent $event)
    {
        if (false === $this->isNotificationsEnabled()) {
            return;
        }

        $user = $this->securityFacade->getLoggedUser();
        $notification = new UserNotification($user, $event->getHeaderText());
        $this->manager->add($notification);
    }

    private function isNotificationsEnabled()
    {
        return (bool)$this->configManager->get('diamante_desk.email_notification');
    }
}
