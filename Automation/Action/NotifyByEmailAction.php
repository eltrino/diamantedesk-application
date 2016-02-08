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

use Diamante\AutomationBundle\Rule\Action\AbstractAction;
use Diamante\DeskBundle\Infrastructure\Notification\NotificationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\UserBundle\Entity\User as OroUser;
use Diamante\DeskBundle\Entity\Ticket;
use Diamante\DeskBundle\Entity\Comment;

class NotifyByEmailAction extends AbstractAction
{
    /**
     * @var NotificationManager
     */
    private $notificationManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param NotificationManager $notificationManager
     * @param ContainerInterface  $container
     */
    public function __construct(NotificationManager $notificationManager, ContainerInterface $container)
    {
        $this->notificationManager = $notificationManager;
        $this->container = $container;
    }

    public function execute()
    {
        /**
         * TODO $emails vars form fact
         */
        $emails = ['akolomiec1989@gmail.com', 'mike@diamantedesk.com'];
        $fact = $this->getContext()->getFact();
        $target = $fact->getTarget();
        $targetType = $fact->getTargetType();
        $provider = sprintf('%s_%s', $targetType, $fact->getAction());

        /**
         * TODO get changes and attachments form fact
         */
        $options = [
            'ticketKey' => $this->getTicketKey($target),
            'changes' => [],
            'attachments' => []
        ];
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

    private function getTicketKey($target)
    {
        if ($target instanceof Ticket) {
            return $target->getKey();
        } elseif ($target instanceof Comment) {
            return $target->getTicket()->getKey();
        }

        return null;
    }
}