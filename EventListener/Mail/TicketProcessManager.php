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
namespace Diamante\DeskBundle\EventListener\Mail;

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketAssigneeWasChanged;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketStatusWasChanged;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasCreated;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUnassigned;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketWasUpdated;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\AttachmentWasAddedToTicket;

class TicketProcessManager extends AbstractMailSubscriber
{
    /**
     * @var array
     */
    private $changeList = array();

    /**
     * @var array
     */
    private $eventsHistory = array();

    /**
     * @var array
     */
    private $attachments = array();

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'ticketWasUpdated'           => 'onTicketWasUpdated',
            'ticketWasCreated'           => 'onTicketWasCreated',
            'attachmentWasAddedToTicket' => 'onAttachmentWasAddedToTicket',
            'ticketStatusWasChanged'     => 'onTicketStatusWasChanged',
            'ticketAssigneeWasChanged'   => 'onTicketAssigneeWasChanged',
            'ticketWasUnassigned'        => 'onTicketWasUnassigned',
        );
    }

    /**
     * @param TicketWasUpdated $event
     */
    public function onTicketWasUpdated(TicketWasUpdated $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->messageHeader = 'Ticket was updated';
        $this->manageEvent($event);

        $this->changeList['Subject']     = $event->getSubject();
        $this->changeList['Description'] = $event->getDescription();
        $this->changeList['Reporter']    = $event->getReporterEmail();
        $this->changeList['Priority']    = $event->getPriority()->getLabel();
        $this->changeList['Status']      = $event->getStatus()->getLabel();
        $this->changeList['Source']      = $event->getSource()->getLabel();
    }

    /**
     * @param TicketWasCreated $event
     */
    public function onTicketWasCreated(TicketWasCreated $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->messageHeader = 'Ticket was created';
        $this->manageEvent($event);

        $this->changeList['Branch']      = $event->getBranchName();
        $this->changeList['Subject']     = $event->getSubject();
        $this->changeList['Description'] = $event->getDescription();
        $this->changeList['Reporter']    = $event->getReporterEmail();
        $this->changeList['Assignee']    = $event->getAssigneeEmail();
        $this->changeList['Priority']    = $event->getPriority()->getLabel();
        $this->changeList['Status']      = $event->getStatus()->getLabel();
        $this->changeList['Source']      = $event->getSource()->getLabel();
    }

    /**
     * @param AttachmentWasAddedToTicket $event
     */
    public function onAttachmentWasAddedToTicket(AttachmentWasAddedToTicket $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->messageHeader = 'Attachments were added to ticket';
        $this->manageEvent($event);
        $this->attachments[] = $event->getAttachmentName();
    }

    /**
     * @param TicketStatusWasChanged $event
     */
    public function onTicketStatusWasChanged(TicketStatusWasChanged $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->messageHeader = 'Ticket status was changed';
        $this->manageEvent($event);

        $this->changeList['Status'] = $event->getStatus()->getLabel();
    }

    /**
     * @param TicketAssigneeWasChanged $event
     */
    public function onTicketAssigneeWasChanged(TicketAssigneeWasChanged $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->messageHeader = 'Ticket assignee was changed';
        $this->manageEvent($event);

        $this->changeList['Assignee'] = $event->getAssigneeEmail();
    }

    /**
     * @param TicketWasUnassigned $event
     */
    public function onTicketWasUnassigned(TicketWasUnassigned $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->messageHeader = 'Ticket was unassigned';
        $this->manageEvent($event);
    }

    /**
     * Send notifications(emails) about ticket updates
     */
    public function process()
    {
        $eventHistory = $this->getEventsHistory();

        if (isset($eventHistory['ticketWasUpdated'])) {
            $this->messageHeader = 'Ticket was updated';
        }

        if (isset($eventHistory['ticketWasCreated'])) {
            $this->messageHeader = 'Ticket was created';
        }

        $options = array (
            'changes'     => $this->changeList,
            'attachments' => $this->attachments,
            'user'        => $this->getUserFullName(),
            'header'      => $this->messageHeader
        );

        $templates = array(
            'txt'  => 'DiamanteDeskBundle:Ticket/notification/mails/update:notification.txt.twig',
            'html' => 'DiamanteDeskBundle:Ticket/notification/mails/update:notification.html.twig'
        );

        $this->sendMessage($options, $templates);
    }

    /**
     * @return array
     */
    public function getEventsHistory()
    {
        return $this->eventsHistory;
    }
}