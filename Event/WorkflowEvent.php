<?php
namespace Diamante\AutomationBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class WorkflowEvent
 * @package Diamante\AutomationBundle\Event
 */
class WorkflowEvent extends Event
{
    /**
     * @var Registry
     */
    protected $doctrineRegistry;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->doctrineRegistry = $registry;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getEntityManager()
    {
        return $this->doctrineRegistry->getManager();
    }
}