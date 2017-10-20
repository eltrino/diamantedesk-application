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

namespace Diamante\AutomationBundle\Automation\JobQueue;


use Diamante\AutomationBundle\Entity\PersistentProcessingContext;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;

class QueueManager
{
    const SELF_EVENT_TRIGGERED_COMMAND_NAME = 'diamante:automation:event:run';
    const QUEUE_NAME                 = 'diamante_automation_event_triggered_rule';

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected $em;

    /**
     * @var array
     */
    protected $rawQueue = [];

    /**
     * @var array
     */
    protected $persistedQueue = [];

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    public function push(PersistentProcessingContext $context)
    {
        $oid = spl_object_hash($context);

        if (array_key_exists($oid, $this->rawQueue)) {
            return;
        }

        $this->rawQueue[$oid] = $context;
    }

    public function persist()
    {
        if (empty($this->rawQueue)) {
            return;
        }

        foreach ($this->rawQueue as $oid => $context) {
            $this->em->persist($context);
            $this->persistedQueue[] = $context;
        }

        $this->em->flush();

        $this->rawQueue = [];
    }

    public function createJobs()
    {
        if (empty($this->persistedQueue)) {
            return;
        }

//        foreach ($this->persistedQueue as $context) {
//            $job = new Job(
//                self::SELF_EVENT_TRIGGERED_COMMAND_NAME,
//                [sprintf('--context-id=%d', $context->getId())],
//                true,
//                self::QUEUE_NAME
//            );
//
//            $this->em->persist($job);
//        }
//
//        $this->em->flush();
//
        $this->persistedQueue = [];
    }

    public function flush()
    {
        $this->persist();
        $this->createJobs();
    }
}