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

use Diamante\AutomationBundle\Infrastructure\Changeset\Changeset;
use Diamante\AutomationBundle\Rule\Action\AbstractAction;
use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Diamante\DeskBundle\Model\Ticket\TicketKey;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Symfony\Component\DependencyInjection\ContainerInterface;

class NotifyByEmailAction extends AbstractAction
{
    const ACTION_NAME = 'notify_by_email';

    /**
     * @var NotificationManager
     */
    private $notificationManager;

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
        $provider = sprintf('%s_%s', $targetType, $action);

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
                unset($changesetDiff['attachments']);
            }

            $additionalOptions['changes'] = $changesetDiff;

            $options = array_merge($options, $additionalOptions);
        }

        foreach ($emails as $email) {
            $recipient = $this->container->get('diamante.user.service')->getUserInstanceByEmail($email);
            $options['isOroUser'] = false;

            /**
             * TODO determine user in render_ticket_url twig extension
             */
            if ($recipient instanceof OroUser) {
                $options['isOroUser'] = true;
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
            return new TicketKey($target['branch']['key'], $target['sequenceNumber']->getValue());
        } elseif ('comment' == $targetType) {
            return $target['ticket']->getKey();
        }

        throw new \RuntimeException('Could not get the key');
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