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

namespace Diamante\AutomationBundle\Model;

use Diamante\DeskBundle\Model\Shared\Entity;

/**
 * Class Schedule
 *
 * @package Diamante\AutomationBundle\Model
 */
class Schedule implements Entity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var string
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $definition;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get command name
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set command name
     *
     * @param  string  $command
     * @return Schedule
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters
     *
     * @param  array  $parameters
     * @return Schedule
     */
    public function setParameters(array $parameters = null)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Returns cron definition string
     *
     * @return string
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Set cron definition string
     *
     * General format:
     * *    *    *    *    *
     * ┬    ┬    ┬    ┬    ┬
     * │    │    │    │    │
     * │    │    │    │    │
     * │    │    │    │    └───── day of week (0 - 6) (0 to 6 are Sunday to Saturday, or use names)
     * │    │    │    └────────── month (1 - 12)
     * │    │    └─────────────── day of month (1 - 31)
     * │    └──────────────────── hour (0 - 23)
     * └───────────────────────── min (0 - 59)
     *
     * Predefined values are:
     *  @yearly (or @annually)  Run once a year at midnight in the morning of January 1                 0 0 1 1 *
     *  @monthly                Run once a month at midnight in the morning of the first of the month   0 0 1 * *
     *  @weekly                 Run once a week at midnight in the morning of Sunday                    0 0 * * 0
     *  @daily                  Run once a day at midnight                                              0 0 * * *
     *  @hourly                 Run once an hour at the beginning of the hour                           0 * * * *
     *
     * @param  string  $definition New cron definition
     * @return Schedule
     */
    public function setDefinition($definition)
    {
        $this->definition = $definition;

        return $this;
    }
}
