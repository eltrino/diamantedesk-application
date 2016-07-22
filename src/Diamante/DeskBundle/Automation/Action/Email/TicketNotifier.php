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
use Diamante\AutomationBundle\EventListener\WorkflowListener;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\UserBundle\Entity\DiamanteUser;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TicketNotifier
 *
 * @package Diamante\AutomationBundle\Automation\Action\Email
 */
class TicketNotifier extends AbstractEntityNotifier implements EntityNotifier
{
    const TICKET_TYPE = 'ticket';

    /**
     * @var array
     */
    protected $propertiesOrder
        = [
            'subject',
            'branch',
            'status',
            'priority',
            'source',
            'reporter',
            'assignee',
            'description'
        ];

    /**
     * @return string
     */
    public function getName()
    {
        return static::TICKET_TYPE;
    }

    /**
     * @return string
     */
    protected function getProvider()
    {
        $pattern = '%s_%s';
        $action = $this->fact->getAction();
        $targetType = $this->fact->getTargetType();

        return sprintf($pattern, $targetType, $action);
    }

    protected function getOptions()
    {
        $target = $this->fact->getTarget();
        $options = ['ticketKey' => new TicketKey($target['branch']->getKey(), $target['sequenceNumber']->getValue())];

        $changesetDiff = $this->changeset->getDiff();
        if (!empty($changesetDiff)) {
            $additionalOptions = $attachments = [];

            if (array_key_exists('attachments', $changesetDiff)) {
                $attachments = $changesetDiff['attachments']['new'];
                $additionalOptions['attachments'] = $attachments;
            }

            $changesetDiff = $this->unsetProperties($changesetDiff);
            $changesetDiff = $this->convertProperties($changesetDiff);
            $changesetDiff = $this->sortTicketProperties($changesetDiff);

            $additionalOptions['changes'] = $changesetDiff;

            $options = array_merge($options, $additionalOptions);
        }

        return $options;
    }

    public function notify()
    {
        $emails = $this->getEmailList();
        $provider = $this->getProvider();
        $options = $this->getOptions();
        $target = $this->fact->getTarget();
        $editor = $this->fact->getEditor();
        $editor = $this->container->get('diamante.user.service')->getByUser($editor);
        $editorName = $this->userService->getFullName($editor);

        if ($this->isChanged()) {
            $ticketId = $target['id'];

            if (!is_null($ticketId)) {
                $ticket = $this->container->get('diamante.ticket.repository')->get($ticketId);
                $this->notificationManager->setTicket($ticket);
            }

            foreach ($emails as $email) {
                $recipient = $this->container->get('diamante.user.service')->getUserInstanceByEmail($email);
                $options = array_merge(
                    $options,
                    [
                        'recipient' => $recipient,
                        'editor'    => $editorName,
                        'target'    => $target
                    ]
                );

                if ($recipient instanceof DiamanteUser) {
                    $options = $this->filterDiamanteUserOptions($options);
                }

                $this->notificationManager->notifyByScenario($provider, $recipient, $options);
            }
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function filterDiamanteUserOptions(array $options) {
        unset($options['changes']['branch']);
        unset($options['changes']['tags']);

        return $options;
    }

    /**
     * don't notify if only tag was changed
     * @return bool
     */
    protected function isChanged()
    {
        $action = $this->fact->getAction();
        $changesetDiff = $this->changeset->getDiff();
        if (empty($changesetDiff) && $action != WorkflowListener::REMOVED) {
            return false;
        }

        return true;
    }

    /**
     * @param array $changeset
     *
     * @return array
     */
    private function sortTicketProperties(array $changeset)
    {
        $sortedChangeset = [];

        foreach ($this->propertiesOrder as $value) {
            if (array_key_exists($value, $changeset)) {
                $sortedChangeset[$value] = $changeset[$value];
            }
        }

        return $sortedChangeset;
    }

    /**
     * @param array $diff
     *
     * @return array
     */
    private function unsetProperties(array $diff)
    {
        unset($diff['attachments']);

        return $diff;
    }

    /**
     * @param array $diff
     *
     * @return array
     */
    private function convertProperties(array $diff)
    {
        if (array_key_exists('assignee', $diff)) {
            $oldAssignee = $diff['assignee']['old'];
            $newAssignee = $diff['assignee']['new'];

            if (!is_null($oldAssignee)) {
                $oldAssignee = $this->reloadUser($oldAssignee);
                $diff['assignee']['old'] = $this->userService->getFullName($oldAssignee);
            }

            $newAssignee = $this->reloadUser($newAssignee);
            $diff['assignee']['new'] = $this->userService->getFullName($newAssignee);
        }

        if (array_key_exists('reporter', $diff)) {
            $oldReporter = $diff['reporter']['old'];
            $newReporter = $diff['reporter']['new'];

            if (!is_null($oldReporter)) {
                $oldReporter = $this->container->get('diamante.user.service')->getByUser($oldReporter);
                $diff['reporter']['old'] = $this->userService->getFullName($oldReporter);
            }

            $newReporter = $this->container->get('diamante.user.service')->getByUser($newReporter);
            $diff['reporter']['new'] = $this->userService->getFullName($newReporter);
        }

        if (array_key_exists('status', $diff)) {
            if (!is_null($diff['status']['old'])) {
                $diff['status']['old'] = Status::getValueToLabelMap()[$diff['status']['old']];
            }

            $diff['status']['new'] = Status::getValueToLabelMap()[$diff['status']['new']];
        }

        if (array_key_exists('priority', $diff)) {
            if (!is_null($diff['priority']['old'])) {
                $diff['priority']['old'] = Priority::getValueToLabelMap()[$diff['priority']['old']];
            }

            $diff['priority']['new'] = Priority::getValueToLabelMap()[$diff['priority']['new']];
        }

        return $diff;
    }

    /**
     * @param array $target
     *
     * @return string
     */
    protected function getReporter(array $target)
    {
        $user = $this->userService->getByUser($target['reporter']);

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
        $watchers = $target['watcherList'];

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
        /** @var OroUser $assignee */
        $assignee = $target['assignee'];
        $assignee = $this->reloadUser($assignee);

        if (!is_null($assignee)) {
            return $assignee->getEmail();
        }

        return null;
    }
}
