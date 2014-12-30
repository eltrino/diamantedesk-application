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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 29/12/14
 * Time: 4:46 PM
 */

namespace Diamante\DeskBundle\Api\Command\Filter;

class FilterTicketsCommand extends CommonFilterCommand
{
    /**
     * @var int
     */
    public $branch;
    /**
     * @var int
     */
    public $assignee;
    /**
     * @var string
     */
    public $reporter;
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $priority;
    /**
     * @var string
     */
    public $status;
    /**
     * @var string
     */
    public $source;
    /**
     * @var string
     */
    public $subject;
}