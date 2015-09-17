<?php
/*
 * Copyright (c) 2014 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Model;

use Diamante\DeskBundle\Model\Shared\Entity;

class Fact implements \Diamante\AutomationBundle\Rule\Fact\Fact
{
    /**
     * @var \Diamante\DeskBundle\Model\Shared\Entity
     */
    protected $target;

    /**
     * @var array
     */
    protected $targetChangeset;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $actionType;

    public function __construct(Entity $target, $targetChangeset, $actionType)
    {
        $this->target = $target;
        $this->targetChangeset = $targetChangeset;
        $this->type = $this->determineType($this->target);
        $this->actionType = $actionType;
    }

    /**
     * @return Entity
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return mixed
     */
    public function getTargetChangeset()
    {
        return $this->targetChangeset;
    }

    /**
     * @return string
     */
    public function getTargetType()
    {
        return $this->type;
    }

    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * @param \Diamante\DeskBundle\Model\Shared\Entity $entity
     * @return string
     */
    protected function determineType(Entity $entity)
    {
        $fullClassName = explode('\\', get_class($entity));
        $class = strtolower(array_pop($fullClassName));

        if (!in_array($class, [Target::TARGET_TYPE_TICKET, Target::TARGET_TYPE_COMMENT])) {
            throw new \RuntimeException('Given target is of unsupported type');
        }

        return $class;
    }
}