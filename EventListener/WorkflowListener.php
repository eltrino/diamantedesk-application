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

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity     = $args->getObject();
        $em         = $args->getEntityManager();
        $changeset  = $em->getUnitOfWork()->getEntityChangeSet($entity);

        if (empty($this->provider->getTargetByClass($entity))) {
            return;
        }

        $processingContext = new PersistentProcessingContext(
            $entity->getId(),
            get_class($entity),
            $changeset
        );

        $this->queueManager->setEntityManager($em);
        $this->queueManager->push($processingContext);
    }
}