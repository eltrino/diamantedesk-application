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

namespace Diamante\AutomationBundle\Rule\Fact;


use Diamante\DeskBundle\Model\Shared\Entity;

class Fact
{
    /**
     * @var Entity
     */
    protected $target;

    /**
     * @var string
     */
    protected $targetType;

    /**
     * @var array|null
     */
    protected $targetChangeset;

    public function __construct($entity, $targetType, $targetChangeset = null)
    {
        $this->target = $entity;
        $this->targetType = $targetType;
        $this->targetChangeset = $targetChangeset;
    }

    /**
     * @return null
     */
    public function getTargetChangeset()
    {
        return $this->targetChangeset;
    }

    /**
     * @return mixed
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }
}