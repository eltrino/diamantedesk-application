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

namespace Diamante\AutomationBundle\EventListener;


use Diamante\AutomationBundle\Automation\JobQueue\QueueManager;
use Diamante\AutomationBundle\Configuration\AutomationConfigurationProvider;
use Diamante\AutomationBundle\Entity\PersistentProcessingContext;
use Doctrine\ORM\Event\LifecycleEventArgs;

class WorkflowListener
{
    const CREATED = 'created';
    const UPDATED = 'updated';
    const REMOVED = 'removed';

    /**
     * @var AutomationConfigurationProvider
     */
    protected $provider;

    /**
     * @var QueueManager
     */
    protected $queueManager;

    public function __construct(
        AutomationConfigurationProvider $provider,
        QueueManager $manager
    )
    {
        $this->provider = $provider;
        $this->queueManager = $manager;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handle($args, static::CREATED);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handle($args, static::UPDATED);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $callback = function ($changeset) {
            array_walk(
                $changeset,
                function (&$item) {
                    $item = array_reverse($item);
                }
            );

            return $changeset;
        };

        $this->handle($args, static::REMOVED, $callback);
    }

    /**
     * @param LifecycleEventArgs $args
     * @param                    $action
     * @param callable|null      $callback
     */
    protected function handle(LifecycleEventArgs $args, $action, $callback = null)
    {
        $entity  = $args->getObject();

        if (empty($this->provider->getTargetByClass($entity))) {
            return;
        }

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($entity));
        $uow->computeChangeSet($metadata, $entity);
        $changeset = $uow->getEntityChangeSet($entity);

        if (is_callable($callback)) {
            $changeset = $callback($changeset);
        }

        $processingContext = new PersistentProcessingContext(
            $entity->getId(),
            get_class($entity),
            $action,
            $changeset
        );

        $this->queueManager->setEntityManager($em);
        $this->queueManager->push($processingContext);
    }
}