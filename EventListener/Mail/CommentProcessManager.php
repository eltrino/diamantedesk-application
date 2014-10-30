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

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\AttachmentWasAddedToComment;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\CommentWasAddedToTicket;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\CommentWasUpdated;
use Diamante\DeskBundle\Model\Ticket\Notifications\Events\TicketStatusWasChanged;

class CommentProcessManager extends AbstractMailSubscriber
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
            'commentWasAddedToTicket'     => 'onCommentWasAddedToTicket',
            'commentWasUpdated'           => 'onCommentWasUpdated',
            'ticketStatusWasChanged'      => 'onTicketStatusWasChanged',
            'attachmentWasAddedToComment' => 'onAttachmentWasAddedToComment',
        );
    }

    /**
     * @param CommentWasAddedToTicket $event
     */
    public function onCommentWasAddedToTicket(CommentWasAddedToTicket $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;
        $this->messageHeader = 'Comment was added to ticket';
        $this->manageEvent($event);

        $this->changeList['Comment'] = $event->getCommentContent();
    }

    /**
     * @param CommentWasUpdated $event
     */
    public function onCommentWasUpdated(CommentWasUpdated $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;
        $this->messageHeader = 'Comment was updated';
        $this->manageEvent($event);

        $this->changeList['Comment'] = $event->getCommentContent();
    }

    /**
     * @param TicketStatusWasChanged $event
     */
    public function onTicketStatusWasChanged(TicketStatusWasChanged $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;
        $this->manageEvent($event);

        $this->changeList['Ticket Status'] = $event->getStatus()->getLabel();
    }

    /**
     * @param AttachmentWasAddedToComment $event
     */
    public function onAttachmentWasAddedToComment(AttachmentWasAddedToComment $event)
    {
        $this->eventsHistory[$event->getEventName()] = true;

        $this->manageEvent($event);
        $this->attachments[] = $event->getAttachmentName();
    }

    /**
     * Send notifications(emails) about updates
     */
    public function process()
    {
        $options = array (
            'changes'     => $this->changeList,
            'attachments' => $this->attachments,
            'user'        => $this->getUserFullName(),
            'header'      => $this->messageHeader
        );

        $templates = array(
            'txt'  => 'DiamanteDeskBundle:Comment/notification/mails/update:notification.txt.twig',
            'html' => 'DiamanteDeskBundle:Comment/notification/mails/update:notification.html.twig'
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