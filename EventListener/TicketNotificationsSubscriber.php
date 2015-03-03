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
namespace Diamante\DeskBundle\EventListener;

use Diamante\DeskBundle\Model\Ticket\Notifications\AttachmentsEvent;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager;
use Diamante\DeskBundle\Model\Ticket\Notifications\Notification;
use Diamante\DeskBundle\Model\Ticket\Notifications\ChangesProviderEvent;
use Diamante\DeskBundle\Model\Ticket\Notifications\NotificationEvent;
use Diamante\DeskBundle\Infrastructure\User\UserStateService;
use Diamante\DeskBundle\Model\Ticket\Notifications\CommentsEvent;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TicketNotificationsSubscriber implements EventSubscriberInterface
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

    /**
     * @var UserStateService
     */
    private $userState;

    private static $events = array(
        'ticketWasCreated' => 'processEvent',
        'ticketWasUpdated' => 'processEvent',
        'attachmentWasAddedToTicket' => 'processEvent',
        'ticketStatusWasChanged'     => 'processEvent',
        'ticketAssigneeWasChanged'   => 'processEvent',
        'ticketWasUnassigned'        => 'processEvent',
        'commentWasAddedToTicket'     => 'processEvent',
        'commentWasUpdated'           => 'processEvent',
        'attachmentWasAddedToComment' => 'processEvent',
        'ticketWasDeleted'            => 'processEvent',
        'commentWasDeleted'           => 'processEvent',
        'attachmentWasDeletedFromTicket' => 'processEvent'
    );

    public function __construct(
        SecurityFacade $securityFacade,
        NotificationDeliveryManager $manager,
        ConfigManager $configManager,
        UserStateService $userState
    ) {
        $this->securityFacade = $securityFacade;
        $this->manager = $manager;
        $this->configManager = $configManager;
        $this->userState = $userState;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return self::$events;
    }

    /**
     * @param NotificationEvent $event
     * @return void
     */
    public function processEvent(NotificationEvent $event)
    {
        if (false === $this->isNotificationsEnabled()) {
            return;
        }

        if ($event instanceof CommentsEvent && $event->getPrivate() && !$this->userState->isOroUser()) {
            return;
        }
        $changeList = new \ArrayIterator();
        if ($event instanceof ChangesProviderEvent) {
            $event->provideChanges($changeList);
        }

        $attachments = array();
        if ($event instanceof AttachmentsEvent) {
            $attachments = $event->attachments();
        }

        $user = $this->securityFacade->getLoggedUser();

        $notification = new Notification(
            $event->getAggregateId(), $user, $event->getHeaderText(),
            $event->getSubject(), $changeList, $attachments
        );

        $this->manager->add($notification);
    }

    private function isNotificationsEnabled()
    {
        return (bool) $this->configManager->get('diamante_desk.email_notification');
    }
}
