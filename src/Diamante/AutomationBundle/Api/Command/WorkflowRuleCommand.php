<?php
namespace Diamante\AutomationBundle\Api\Command;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EventTriggeredRuleCommand
 *
 * @package Diamante\AutomationBundle\Api\Command
 */
class EventTriggeredRuleCommand
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $conditions;

    /**
     * @var array
     */
    public $actions;

    /**
     * @var string
     */
    public $mode;
}
