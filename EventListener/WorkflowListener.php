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

use Diamante\AutomationBundle\Model\ListedEntity\ListedEntitiesProvider;
use Diamante\AutomationBundle\Model\ListedEntity\ProcessorInterface;
use Diamante\DeskBundle\Event\WorkflowEvent;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Diamante\DeskBundle\Model\Shared\Entity;

class WorkflowListener
{
    const DELETE_ACTION = 'delete';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var WorkflowEvent
     */
    private $event;

    /**
     * @var array
     */
    private $listedEntities = [];

    /**
     * @var ListedEntitiesProvider
     */
    private $listedEntitiesProvider;

    /**
     * @var ProcessorInterface
     */
    private $listedEntityProcessor;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->listedEntitiesProvider = $container->get('diamante_automation.provider.listed_entities');
        $this->listedEntities = $this->listedEntitiesProvider->provideListedEntities();
    }

    /**
     * @param WorkflowEvent $event
     */
    public function processWorkflowRule(WorkflowEvent $event)
    {

        $this->event = $event;
        $entity = $event->getEntity();

        foreach ($this->listedEntities as $listedEntity) {
            $listedEntityClassName = $listedEntity->getListedEntityClassName();
            if ($entity instanceof $listedEntityClassName) {
                $this->listedEntityProcessor = $listedEntity->getEntityProcessor();
                break;
            }
        }

        if (!$this->listedEntityProcessor) {
            return;
        }


        try {
            $this->processEntity($entity);
        } catch (\RuntimeException $e) {
            $this->container->get('monolog.logger.diamante')->error(
                sprintf('Rule processing failed: %s', $e->getMessage())
            );
        }

    }

    /**
     * @param Entity $entity
     */
    protected function processEntity(Entity $entity)
    {
        $engine = $this->container->get('diamante_automation.engine');
        $lastEntityLog = $this->getLastEntityLog($entity);
        $changes = $this->computeChanges($entity, $lastEntityLog);
        $actionType = $this->getActionType($lastEntityLog);
        $fact = $engine->createFact($entity, $changes, $actionType);

        if ($engine->check($fact)) {
            $engine->runAgenda();
        }
        $engine->reset();
    }

    /**
     * @param Entity $entity
     *
     * @return Audit
     */
    protected function getLastEntityLog(Entity $entity)
    {
        $repository = $this->event->getEntityManager()->getRepository('OroDataAuditBundle:Audit');
        $entityLog = $repository->getLogEntries($entity);

        /** @var Audit $lastEntityLog */
        return array_shift($entityLog);
    }

    /**
     * @param Entity $entity
     * @param Audit  $lastEntityLog
     *
     * @return array
     */
    protected function computeChanges(Entity $entity, Audit $lastEntityLog = null)
    {
        $changes = [];

        if (!$lastEntityLog) {
            return $changes;
        }

        return $this->listedEntityProcessor->getEntityChanges($entity, $lastEntityLog, $this->event);
    }

    /**
     * @param Audit $lastEntityLog
     *
     * @return string
     */
    protected function getActionType(Audit $lastEntityLog = null)
    {
        if(!$lastEntityLog) {
            return self::DELETE_ACTION;
        }

        return $lastEntityLog->getAction();
    }
}