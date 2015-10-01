<?php
namespace Diamante\DeskBundle\Api\Internal\Shared;

use Diamante\DeskBundle\Event\WorkflowEvent;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class WorkflowTrait
 *
 * @package Diamante\DeskBundle\Api\Internal
 */
trait WorkflowTrait
{
    /**
     * @param Registry                 $doctrineRegistry
     * @param EventDispatcherInterface $dispatcher
     * @param                          $entity
     */
    public function dispatchWorkflowEvent(Registry $doctrineRegistry, EventDispatcherInterface $dispatcher, $entity)
    {
        try {
            $workFlowEvent = new WorkflowEvent($doctrineRegistry, $entity);
        } catch (\Exception $e) {
            return;
        }

        $dispatcher->dispatch('automation.workflow.process', $workFlowEvent);
    }
}