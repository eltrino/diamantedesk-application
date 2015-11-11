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
namespace Diamante\AutomationBundle\Api\Command;

use JMS\Serializer\Annotation\Type;
use Diamante\AutomationBundle\Api\Internal\RuleServiceImpl;

/**
 * Class ActionCommand
 *
 * @package Diamante\AutomationBundle\Api\Command
 */
class ActionCommand
{
    /**
     * @var int
     *
     * @Type("string")
     */
    public $id;

    /**
     * @Type("string")
     */
    public $action;

    /**
     * @Type("string")
     */
    public $property;

    /**
     * @Type("string")
     */
    public $value;

    public static function createFromAction($actionEntity)
    {
        $command = new self;
        $command->id = $actionEntity->getId();
        list($command->action, $command->property, $command->value) = RuleServiceImpl::parseAction(
            $actionEntity->getAction()
        );

        return $command;
    }
}
