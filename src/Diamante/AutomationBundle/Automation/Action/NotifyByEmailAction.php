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

namespace Diamante\AutomationBundle\Automation\Action;

use Diamante\AutomationBundle\EventListener\WorkflowListener;
use Diamante\AutomationBundle\Infrastructure\Changeset\Changeset;
use Diamante\AutomationBundle\Rule\Action\AbstractAction;
use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Diamante\DeskBundle\Model\Ticket\Priority;
use Diamante\DeskBundle\Model\Ticket\Status;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Diamante\UserBundle\Entity\DiamanteUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotifyByEmailAction extends AbstractAction
{
    const ACTION_NAME = 'notify_by_email';
    const COMMENT_TARGET = 'comment';
    const TICKET_TARGET = 'ticket';

    /**
     * @var NotificationManager
     */
    private $notificationManager;

    private $ticketPropertiesOrder
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
     * @var ContainerInterface
     */
    private $container;

    public function execute()
    {
        $context = $this->getContext();
        $fact = $context->getFact();
        $parameters = $context->getParameters()->all();
        $target = $fact->getTarget();
        $emails = $this->container->get('diamante.automation.email.resolver')->getList(
            $target,
            $fact->getTargetType(),
            $parameters
        );
        $targetType = $fact->getTargetType();
        $action = $fact->getAction();

        $pattern = '%s_%s';

        if (array_key_exists('private', $target) && $target['private']) {
            $pattern = 'private_%s_%s';
        }

        $provider = sprintf($pattern, $targetType, $action);

        /**
         * TODO get changes and attachments form fact
         */
        $options = ['ticketKey' => $this->getTicketKey($target, $targetType)];

        $changeset = new Changeset($fact->getTargetChangeset(), $action);
        $changesetDiff = $changeset->getDiff();
        if (!empty($changesetDiff)) {
            $additionalOptions = $attachments = [];

            if (array_key_exists('attachments', $changesetDiff)) {
                $attachments = $changesetDiff['attachments']['new'];
                $additionalOptions['attachments'] = $attachments;
            }

            $changesetDiff = $this->unsetProperties($changesetDiff);
            $changesetDiff = $this->convertProperties($changesetDiff);

            if (static::TICKET_TARGET == $targetType) {
                $changesetDiff = $this->sortTicketProperties($changesetDiff);
            }

            $additionalOptions['changes'] = $changesetDiff;

            $options = array_merge($options, $additionalOptions);
        }

        // if only tag was changed
        if (empty($changesetDiff) && $action != WorkflowListener::REMOVED) {
            return $this;
        }

        foreach ($emails as $email) {
            $recipient = $this->container->get('diamante.user.service')->getUserInstanceByEmail($email);
            $options['recipient'] = $recipient;

            if (static::COMMENT_TARGET == $targetType && $recipient instanceof DiamanteUser && $target['private']) {
                continue;
            }

            $this->notificationManager->notifyByScenario($provider, $recipient, $options);
        }
    }

    /**
     * @param array  $target
     * @param string $targetType
     *
     * @return TicketKey
     */
    private function getTicketKey(array $target, $targetType)
    {
        if ('ticket' == $targetType) {
            return new TicketKey($target['branch']->getKey(), $target['sequenceNumber']->getValue());
        } elseif ('comment' == $targetType) {
            return $target['ticket']->getKey();
        }

        throw new \RuntimeException('Could not get the key');
    }

    /**
     * @param array $changeset
     *
     * @return array
     */
    private function sortTicketProperties(array $changeset)
    {
        $sortedChangeset = [];

        foreach ($this->ticketPropertiesOrder as $value) {
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
    private function convertProperties(array $diff)
    {
        if (array_key_exists('reporter', $diff)) {
            $oldReporter = $diff['reporter']['old'];
            $newReporter = $diff['reporter']['new'];

            if (!is_null($oldReporter)) {
                $diff['reporter']['old'] = $this->container->get('diamante.user.service')->getByUser($oldReporter);
            }

            $diff['reporter']['new'] = $this->container->get('diamante.user.service')->getByUser($newReporter);
        }

        if (array_key_exists('author', $diff)) {
            $oldAuthor = $diff['author']['old'];
            $newAuthor = $diff['author']['new'];

            if (!is_null($oldAuthor)) {
                $diff['author']['old'] = $this->container->get('diamante.user.service')->getByUser($oldAuthor);
            }

            $diff['author']['new'] = $this->container->get('diamante.user.service')->getByUser($newAuthor);
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
     * @param array $parameters
     */
    public function addParameters(array $parameters)
    {
        $this->getContext()->addParameter($parameters[static::ACTION_NAME]);
    }

    /**
     * @param ContainerInterface $container
     *
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param NotificationManager $notificationManager
     */
    public function setNotificationManager(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

}