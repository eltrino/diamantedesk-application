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

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasCreated;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUpdated;
use Diamante\DeskBundle\Infrastructure\Ticket\Notification\Notifier;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TicketSubscriber implements EventSubscriberInterface
{
    private $ticketWasUpdatedNotifiers = array();
    private $ticketWasCreatedNotifiers = array();

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
           'ticketWasUpdated' => 'onTicketWasUpdated',
           'ticketWasCreated' => 'onTicketWasCreated'
        );
    }

    /**
     * @param TicketWasUpdated $event
     */
    public function onTicketWasUpdated(TicketWasUpdated $event)
    {
        foreach ($this->ticketWasUpdatedNotifiers as $notifier) {
            $notifier->notify($event);
        }
    }

    /**
     * @param TicketWasCreated $event
     */
    public function onTicketWasCreated(TicketWasCreated $event)
    {
        foreach ($this->ticketWasCreatedNotifiers as $notifier) {
            $notifier->notify($event);
        }
    }

    /**
     * Register a notifier
     *
     * @param Notifier $notifier
     */
    public function registerTicketWasUpdatedNotifiers(Notifier $notifier)
    {
        $this->ticketWasUpdatedNotifiers[] = $notifier;
    }

    /**
     * Register a notifier
     *
     * @param Notifier $notifier
     */
    public function registerTicketWasCreatedNotifiers(Notifier $notifier)
    {
        $this->ticketWasCreatedNotifiers[] = $notifier;
    }
} 