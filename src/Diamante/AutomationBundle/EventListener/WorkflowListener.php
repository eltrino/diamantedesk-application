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
use Diamante\AutomationBundle\Infrastructure\Changeset\ChangesetBuilder;
use Doctrine\ORM\Event\LifecycleEventArgs;

class WorkflowListener
{
    const CREATED = 'created';
    const UPDATED = 'updated';
    const REMOVED = 'removed';

    const TICKET_TARGET = 'ticket';
    const COMMENT_TARGET = 'comment';

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
        $entity = $args->getObject();
        $target = $this->provider->getTargetByEntity($entity);

        if (empty($target)) {
            return;
        }

        $em = $args->getEntityManager();
        $processingContext = new PersistentProcessingContext(
            $entity->getId(),
            $this->provider->getClassByTarget($target),
            $action,
            $getChangeset($entity)
        );

        $disableListeners = true;

        // don't disable listeners if you edit both comment and ticket status on create action
        if (static::COMMENT_TARGET == $target) {
            $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity->getTicket());

            if (isset($changeset['status'])) {
                $disableListeners = false;
            }
        }

        // don't disable listeners when you save comment after ticket status and comment content update on update action
        // don't disable listeners when tickets remove from mass action
        if (static::TICKET_TARGET == $target && in_array($action, [static::UPDATED, static::REMOVED])) {
            $disableListeners = false;
        }

        if ($disableListeners) {
            $em->getEventManager()->disableListeners();
        }

        $this->queueManager->setEntityManager($em);
        $this->queueManager->push($processingContext);
    }
}
