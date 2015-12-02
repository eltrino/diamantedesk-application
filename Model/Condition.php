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

    public function __construct(
        $type,
        $parameters,
        Group $group
    ) {
        $this->id = Uuid::uuid4();
        $this->type = $type;
        $this->parameters = $parameters;
        $this->group = $group;
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

    public function getGroup()
    {
        return $this->group;
    }

    public function update(
        $type,
        $parameters
    ) {
        $this->type = $type;
        $this->parameters = $parameters;
    }
}