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

namespace Diamante\AutomationBundle\Model\ListedEntity\Processor;

use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\AutomationBundle\Action\Strategy\EmailNotificationStrategy\EmailTemplate;
use Diamante\DeskBundle\Event\WorkflowEvent;
use Diamante\DeskBundle\Model\Shared\Entity;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Oro\Bundle\DataAuditBundle\Entity\Audit;

class TicketProcessor extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @param Entity|Ticket $entity
     * @param Audit $entityLog
     * @param WorkflowEvent $event
     * @return mixed
     */
    public function getEntityChanges(Entity $entity, Audit $entityLog, WorkflowEvent $event)
    {
        $changes = $this->extractChanges($entityLog);

        $repository = $event->getEntityManager()->getRepository('OroDataAuditBundle:Audit');

        /** @var  $attachment */
        foreach ($entity->getAttachments() as $attachment) {
            $attachmentLog = $repository->getLogEntries($attachment);
            $lastAttachmentLog = array_shift($attachmentLog);
            $changes['attachments'] = $this->extractChanges($lastAttachmentLog);
        }

        return $changes;
    }

    /**
     * @return array
     */
    public function getEntityEmailTemplates()
    {
        return [
            EmailTemplate::TEMPLATE_TYPE_HTML => 'DiamanteDeskBundle:Ticket/notification:notification.html.twig',
            EmailTemplate::TEMPLATE_TYPE_TXT  => 'DiamanteDeskBundle:Ticket/notification:notification.txt.twig',
        ];
    }
}