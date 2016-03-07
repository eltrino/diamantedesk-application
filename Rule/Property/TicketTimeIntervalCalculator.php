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

namespace Diamante\AutomationBundle\Rule\Property;

use Diamante\AutomationBundle\Entity\PersistentProcessingContext;
use Diamante\AutomationBundle\EventListener\WorkflowListener;
use Diamante\AutomationBundle\Infrastructure\Shared\TargetMapper;
use Doctrine\ORM\EntityManager;

class TicketTimeIntervalCalculator
{
    /**
     * @var EntityManager
     */
    protected $em;

    protected $processingContextRepository;


    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->processingContextRepository = $this->em->getRepository(
            'DiamanteAutomationBundle:PersistentProcessingContext'
        );
    }

    public function sinceCreate(array $target)
    {
        /** @var PersistentProcessingContext $processingContext */
        $processingContext = $this->processingContextRepository
            ->findOneBy(['targetEntityId' => $target['id'], 'action' => WorkflowListener::CREATED]);
        $target = TargetMapper::fromChangeset($processingContext->getTargetEntityChangeset());
        $hours = $this->getDiff($target['createdAt']);

        return $hours;
    }

    public function sinceLastUpdate(array $target)
    {
        /** @var PersistentProcessingContext $processingContext */
        $processingContext = $this->processingContextRepository
            ->findOneBy(['targetEntityId' => $target['id'], 'action' => WorkflowListener::UPDATED]);
        $target = TargetMapper::fromChangeset($processingContext->getTargetEntityChangeset());
        $hours = $this->getDiff($target['createdAt']);

        return $hours;

    }

    private function getDiff($time)
    {
        $now = new \DateTime();
        $timestampDiff = $now->getTimestamp() - $time->getTimestamp();
        $hours = floor($timestampDiff / (60 * 60));

        return $hours;
    }
}