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
namespace Diamante\DeskBundle\Tests\EventListener;

use Diamante\DeskBundle\EventListener\TicketNotificationsSubscriber;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Oro\Bundle\UserBundle\Entity\User;

class TicketNotificationsSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TicketNotificationsSubscriber
     */
    private $subscriber;

    /**
     * @var \Oro\Bundle\SecurityBundle\SecurityFacade
     * @Mock \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    private $securityFacade;

    /**
     * @var \Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager
     * @Mock \Diamante\DeskBundle\Model\Ticket\Notifications\NotificationDeliveryManager
     */
    private $notificationDeliveryManager;

    /**
     * @var \Oro\Bundle\ConfigBundle\Config\ConfigManager
     * @Mock \Oro\Bundle\ConfigBundle\Config\ConfigManager
     */
    private $configManager;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->subscriber = new TicketNotificationsSubscriber(
            $this->securityFacade, $this->notificationDeliveryManager, $this->configManager
        );
    }

    public function testProcessEventWhenNotificationsIsDisabled()
    {
        $this->configManager->expects($this->once())->method('get')->with('diamante_desk.email_notification')
            ->will($this->returnValue(false));

        $this->notificationDeliveryManager->expects($this->never())->method('add');

        $event = $this->event();
        $this->subscriber->processEvent($event);
    }

    public function testProcessEvent()
    {
        $user = new User();
        $user->setId(1);
        $this->configManager->expects($this->once())->method('get')->with('diamante_desk.email_notification')
            ->will($this->returnValue(true));
        $this->securityFacade->expects($this->once())->method('getLoggedUser')->will($this->returnValue($user));
        $event = $this->event();

        $this->notificationDeliveryManager->expects($this->once())->method('add')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf('\Diamante\DeskBundle\Model\Ticket\Notifications\Notification'),
                    $this->attributeEqualTo('ticketUniqueId', $event->getAggregateId()),
                    $this->attributeEqualTo('author', $user),
                    $this->attributeEqualTo('headerText', $event->getHeaderText()),
                    $this->attributeEqualTo('subject', $event->getSubject()),
                    $this->attributeEqualTo('attachments', $event->attachments()),
                    $this->attribute(
                        $this->logicalAnd(
                            $this->isInstanceOf('\ArrayIterator'),
                            $this->arrayHasKey('Description'),
                            $this->contains('New Description')
                        ), 'changeList')
                )
            );

        $this->subscriber->processEvent($event);
    }

    private function event()
    {
        $attachments = array('file.ext');
        $changes = array('Description' => 'New Description');
        $aggregateId = '1g2';
        $eventName = 'DummyEventName';
        $headerTxt = 'Text description about event';
        $subject = 'Dummy Subject';

        return new Event($attachments, $changes, $aggregateId, $eventName, $headerTxt, $subject);
    }
}
