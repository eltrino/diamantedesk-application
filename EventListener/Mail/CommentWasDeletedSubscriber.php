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

use Diamante\DeskBundle\Model\Ticket\Notifications\Events\CommentWasDeleted;

class CommentWasDeletedSubscriber extends AbstractMailSubscriber
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'commentWasDeleted' => 'onCommentWasDeleted',
        );
    }

    /**
     * @param CommentWasDeleted $event
     */
    public function onCommentWasDeleted(CommentWasDeleted $event)
    {
        $this->messageHeader = 'Comment was deleted';
        $this->manageEvent($event);

        $options = array (
            'comment' => $event->getCommentContent(),
            'user'    => $this->getUserFullName(),
            'header'  => $this->messageHeader
        );

        $templates = array(
            'txt'  => 'DiamanteDeskBundle:Comment/notification/mails/delete:notification.txt.twig',
            'html' => 'DiamanteDeskBundle:Comment/notification/mails/delete:notification.html.twig'
        );

        $this->sendMessage($options, $templates);
    }
} 