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
use Diamante\AutomationBundle\Infrastructure\Shared\ChangesetBuilder;
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

    /**
     * @var ChangesetBuilder
     */
    protected $changesetBuilder;

    public function __construct(
        AutomationConfigurationProvider $provider,
        QueueManager $manager,
        ChangesetBuilder $changesetBuilder
    )
    {
        $this->provider = $provider;
        $this->queueManager = $manager;
        $this->changesetBuilder = $changesetBuilder;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->handle($args, static::CREATED, function($entity) {
            return $this->changesetBuilder->getChangesetForCreateAction($entity);
        });
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->handle($args, static::UPDATED, function($entity) {
            return $this->changesetBuilder->getChangesetForUpdateAction($entity);
        });
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $this->handle($args, static::REMOVED, function($entity) {
            return $this->changesetBuilder->getChangesetForRemoveAction($entity);
        });
    }

    /**
     * @param LifecycleEventArgs $args
     * @param                    $action
     * @param callable|null      $getChangeset
     */
    protected function handle(LifecycleEventArgs $args, $action, $getChangeset)
    {
        $entity  = $args->getObject();

        $em = $args->getEntityManager();
        $processingContext = new PersistentProcessingContext(
            $entity->getId(),
            get_class($entity),
            $action,
            $getChangeset($entity)
        );

        $this->queueManager->setEntityManager($em);
        $this->queueManager->push($processingContext);
    }
}
