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
 


namespace Diamante\AutomationBundle\Action;


class ActionHandler
{
    public static function getInstance()
    {
        return new self();
    }

    public function parse($string)
    {
        $matches = $actionEntity = [];
        $result = preg_match('/^(\w+)\[(.*):{(.*)}\]/', $string, $matches);

        if (!$result) {
            throw new \RuntimeException('Action of unknown type is configured');
        }

        return [$matches[1], $matches[2], $matches[3]];
    }

    public function create($command)
    {
        return sprintf('%s[%s:{%s}]', $command->action, $command->property, $command->value);
    }


}