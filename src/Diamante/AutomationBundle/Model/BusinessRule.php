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

/**
 * Class BusinessRule
 *
 * @package Diamante\AutomationBundle\Model
 */
class BusinessRule extends Rule
{
    /**
     * @var string
     */
    protected $timeInterval;

    /**
     * BusinessRule constructor.
     *
     * @param string $name
     * @param string $target
     * @param string $timeInterval
     * @param bool   $status
     */
    public function __construct($name, $target, $timeInterval, $status = true)
    {
        parent::__construct($name, $target, $status);
        $this->timeInterval = $timeInterval;
    }

    /**
     * @param string $name
     * @param string $timeInterval
     * @param bool   $status
     */
    public function update($name, $timeInterval, $status)
    {
        $this->name = $name;
        $this->timeInterval = $timeInterval;
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getTimeInterval()
    {
        return $this->timeInterval;
    }
}