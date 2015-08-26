<?php
namespace Diamante\DeskBundle\Event;

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

    protected $entity;

    /**
     * @param Registry $registry
     * @param $entity
     */
    public function __construct(Registry $registry, $entity)
    {
        $this->doctrineRegistry = $registry;
        $this->entity = $entity;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    public function getEntityManager()
    {
        return $this->doctrineRegistry->getManager();
    }

    /**
     * @return array
     */
    public function getEntity()
    {
        return $this->entity;
    }
}