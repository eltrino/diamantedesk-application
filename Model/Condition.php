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

    protected $type;

    protected $parameters;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var int
     */
    protected $weight;

    public function __construct(
        $type,
        $parameters,
        Group $group,
        $weight = 0
    ) {
        $this->id = Uuid::uuid4();
        $this->type = $type;
        $this->parameters = $parameters;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function update(
        $type,
        $parameters,
        $weight = 0
    ) {
        $this->type = $type;
        $this->parameters = $parameters;
        $this->weight = $weight;
    }
}