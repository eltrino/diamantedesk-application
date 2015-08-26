<?php
namespace Diamante\DeskBundle\Api\Internal\Shared;

use Diamante\AutomationBundle\Event\WorkflowEvent;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class WorkflowTrait
 * @package Diamante\DeskBundle\Api\Internal
 */
trait WorkflowTrait
{
    /**
     * @param Registry $doctrineRegistry
     * @param EventDispatcher $dispatcher
     * @param $entity
     */
    public function dispatchWorkflowEvent(Registry $doctrineRegistry, EventDispatcher $dispatcher, $entity)
    {
        try {
            $workFlowEvent = new WorkflowEvent($doctrineRegistry, $entity);
        } catch (\Exception $e) {
            return;
        }

        $dispatcher->dispatch('automation.workflow.process', $workFlowEvent);
    }
}