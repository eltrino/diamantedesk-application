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

namespace Diamante\AutomationBundle\EventListener;

use Diamante\AutomationBundle\Event\WorkflowEvent;
use Diamante\DeskBundle\Model\Ticket\Comment;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Oro\Bundle\DataAuditBundle\Entity\AuditField;
use Diamante\AutomationBundle\Model\Change;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Diamante\DeskBundle\Model\Shared\Entity;

class WorkflowListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var WorkflowEvent
     */
    private $event;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param WorkflowEvent $event
     */
    public function processWorkflowRule(WorkflowEvent $event)
    {
        $this->event = $event;
        $entity = $event->getEntity();

        if ($entity instanceof Ticket || $entity instanceof Comment) {
            try {
                $this->processEntity($entity);
            } catch (\RuntimeException $e) {
                $this->container->get('monolog.logger.diamante')->error(
                    sprintf('Rule processing failed: %s', $e->getMessage())
                );
            }
        }
    }

    /**
     * @param Entity $entity
     */
    protected function processEntity(Entity $entity)
    {
        $engine = $this->container->get('diamante_automation.engine');

        $fact = $engine->createFact($entity, $this->computeChanges($entity));

        if ($engine->check($fact)) {
            $engine->runAgenda();
        }
        $engine->reset();
    }

    /**
     * @param Entity $entity
     * @return array
     */
    protected function computeChanges(Entity $entity)
    {
        $repository = $this->event->getEntityManager()->getRepository('OroDataAuditBundle:Audit');
        $entityLog = $repository->getLogEntries($entity);

        /** @var Audit $lastEntityLog */
        $lastEntityLog = array_shift($entityLog);

        $changes = [];

        if (!$lastEntityLog) {
            return $changes;
        }


        switch (true) {
            case $entity instanceof Ticket:
                $changes = $this->computeTicketChanges($entity, $lastEntityLog);
                break;
            case $entity instanceof Comment:
                $changes = $this->computeCommentChanges($entity, $lastEntityLog);
                break;
        }

        return $changes;
    }

    /**
     * @param Ticket $entity
     * @param Audit $entityLog
     * @return array
     */
    private function computeTicketChanges(Ticket $entity, Audit $entityLog)
    {
        $changes = $this->extractChanges($entityLog);

        $repository = $this->event->getEntityManager()->getRepository('OroDataAuditBundle:Audit');

        /** @var  $attachment */
        foreach ($entity->getAttachments() as $attachment) {
            $attachmentLog = $repository->getLogEntries($attachment);
            $lastAttachmentLog = array_shift($attachmentLog);
            $changes['attachments'] = $this->extractChanges($lastAttachmentLog);
        }

        return $changes;
    }

    /**
     * @param Comment $entity
     * @param Audit $entityLog
     * @return array
     */
    private function computeCommentChanges(Comment $entity, Audit $entityLog)
    {
        return $this->extractChanges($entityLog);
    }

    /**
     * @param Audit $entityLog
     * @return array
     */
    private function extractChanges(Audit $entityLog)
    {
        $changes = [];
        /** @var AuditField $field */
        foreach ($entityLog->getFields()->toArray() as $field) {
            $changes[] = new Change(
                $field->getField(),
                $field->getOldValue(),
                $field->getNewValue()
            );
        }
        return $changes;
    }

}