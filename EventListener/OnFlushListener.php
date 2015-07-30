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

use Diamante\DeskBundle\Model\Ticket\Comment;
use Diamante\DeskBundle\Model\Ticket\Ticket;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Diamante\DeskBundle\Model\Shared\Entity;

class OnFlushListener implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }

    /**
     * @param OnFlushEventArgs $event
     */
    public function onFlush(OnFlushEventArgs $event)
    {
        $this->unitOfWork = $event->getEntityManager()->getUnitOfWork();

        $entities = array_merge(
            $this->unitOfWork->getScheduledEntityInsertions(),
            $this->unitOfWork->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
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
    }

    /**
     * @param Entity $entity
     */
    protected function processEntity(Entity $entity)
    {
        $engine = $this->container->get('diamante_automation.engine');
        $fact = $engine->createFact($entity, $this->unitOfWork->getEntityChangeSet($entity));

        if ($engine->check($fact)) {
            $engine->runAgenda();
        }
        $engine->reset();
    }
}