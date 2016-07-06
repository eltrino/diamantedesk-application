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

namespace Diamante\DeskBundle\Automation\Action\Email;

use Diamante\AutomationBundle\Automation\Action\Email\AbstractEntityNotifier;
use Diamante\AutomationBundle\Automation\Action\Email\EntityNotifier;
use Diamante\UserBundle\Entity\DiamanteUser;
use Oro\Bundle\UserBundle\Entity\User as OroUser;

/**
 * Class CommentNotifier
 *
 * @package Diamante\DeskBundle\Automation\Action\Email
 */
class CommentNotifier extends AbstractEntityNotifier implements EntityNotifier
{
    const COMMENT_TYPE = 'comment';

    public function getName()
    {
        return static::COMMENT_TYPE;
    }

    /**
     * @return string
     */
    protected function getProvider()
    {
        $pattern = '%s_%s';
        $action = $this->fact->getAction();
        $target = $this->fact->getTarget();
        $targetType = $this->fact->getTargetType();

        if ($target['private']) {
            $pattern = 'private_%s_%s';
        }

        return sprintf($pattern, $targetType, $action);
    }

    /**
     * @param array $diff
     *
     * @return array
     */
    protected function unsetProperties(array $diff)
    {
        if (array_key_exists('private', $diff)) {
            unset($diff['private']);
        }

        if (array_key_exists('attachments', $diff)) {
            unset($diff['attachments']);
        }

        return $diff;
    }

    /**
     * @param array $diff
     *
     * @return array
     */
    protected function convertProperties(array $diff)
    {
        if (array_key_exists('author', $diff)) {
            $oldAuthor = $diff['author']['old'];
            $newAuthor = $diff['author']['new'];

            if (!is_null($oldAuthor)) {
                $user = $this->container->get('diamante.user.service')->getByUser($oldAuthor);
                $diff['author']['old'] = $this->getUserName($user);
            }

            $user = $this->container->get('diamante.user.service')->getByUser($newAuthor);
            $diff['author']['new'] = $this->getUserName($user);
        }

        return $diff;
    }

    public function notify()
    {
        $target = $this->fact->getTarget();
        $emails = $this->getEmailList();
        $provider = $this->getProvider();
        $options = $this->getOptions();
        $ticketId = $target['ticket']->getId();

        if (!is_null($ticketId)) {
            $ticket = $this->container->get('diamante.ticket.repository')->get($ticketId);
            $this->notificationManager->setTicket($ticket);
        }

        foreach ($emails as $email) {
            $recipient = $this->container->get('diamante.user.service')->getUserInstanceByEmail($email);
            $options['recipient'] = $recipient;

            if ($recipient instanceof DiamanteUser && $target['private']) {
                continue;
            }

            $this->notificationManager->notifyByScenario($provider, $recipient, $options);
        }
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $target = $this->fact->getTarget();
        $options = ['ticketKey' => $target['ticket']->getKey()];

        $changesetDiff = $this->changeset->getDiff();
        if (!empty($changesetDiff)) {
            $additionalOptions = $attachments = [];

            if (array_key_exists('attachments', $changesetDiff)) {
                $attachments = $changesetDiff['attachments']['new'];
                $additionalOptions['attachments'] = $attachments;
            }

            $changesetDiff = $this->unsetProperties($changesetDiff);
            $changesetDiff = $this->convertProperties($changesetDiff);

            $additionalOptions['changes'] = $changesetDiff;

            $options = array_merge($options, $additionalOptions);
        }

        return $options;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getAuthor(array $target)
    {
        $user = $this->userService->getByUser($target['author']);

        return $user->getEmail();
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getReporter(array $target)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        $ticket = $target['ticket'];
        $user = $this->userService->getByUser($ticket->getReporter());

        return $user->getEmail();
    }

    /**
     * @param array $target
     *
     * @return array
     */
    protected function getWatchers(array $target)
    {
        $list = [];
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        $ticket = $target['ticket'];
        $watchers = $ticket->getWatcherList();

        foreach ($watchers as $watcher) {
            $user = $this->userService->getByUser($watcher->getUserType());
            $list[] = $user->getEmail();
        }

        return $list;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getAssignee(array $target)
    {
        /** @var \Diamante\DeskBundle\Entity\Ticket $ticket */
        $ticket = $target['ticket'];
        /** @var array|DiamanteUser $assignee */
        $assignee = $ticket->getAssignee();

        if (!empty($assignee)) {
            if ($assignee instanceof OroUser) {
                /**
                 * Reloading oro user because it loses email after execute unserialize method
                 *
                 * @var OroUser $user
                 */
                $user = $this->oroUserManager->findUserBy(['id' => $assignee->getId()]);
                if (!is_null($user)) {
                    return $user->getEmail();
                }

                return null;
            }

            return $assignee->getEmail();
        }

        return null;
    }

    /**
     * @param DiamanteUser|OroUser $user
     *
     * @return string
     */
    private function getUserName($user)
    {
        if ($user instanceof DiamanteUser) {
            return $user->getFullName();
        }

        return sprintf('%s %s', $user->getFirstName(), $user->getLastName());
    }
}
