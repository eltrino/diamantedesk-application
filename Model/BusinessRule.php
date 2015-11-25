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

class BusinessRule extends Rule
{
    protected $timeInterval;

    public function __construct($name, $timeInterval, $active = true)
    {
        parent::__construct($name, $active);
        $this->timeInterval = $timeInterval;
    }

    public function update($name, $timeInterval)
    {
        $this->name = $name;
        $this->timeInterval = $timeInterval;
    }

    public function getTimeInterval()
    {
        return $this->timeInterval;
    }
}