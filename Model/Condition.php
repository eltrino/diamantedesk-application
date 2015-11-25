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
use Rhumsaa\Uuid\Uuid;

class Condition implements Entity
{
    /**
     * @var int
     */
    protected $id;

    protected $condition;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var int
     */
    protected $weight;

    public function __construct(
        $condition,
        Group $group,
        $weight = 0
    ) {
        $this->id = (string)Uuid::uuid4();
        $this->condition = $condition;
        $this->group = $group;
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    public function update(
        $condition,
        $weight
    ) {
        $this->condition = $condition;
        $this->weight = $weight;
    }
}