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

namespace Diamante\AutomationBundle\Rule\Action\Entity\Specific;

use Diamante\AutomationBundle\Rule\Action\Entity\AbstractAction;

/**
 * Class NotifyByEmail
 *
 * @package Diamante\AutomationBundle\Rule\Action\Entity\Specific
 */
class UpdateProperty extends AbstractAction
{
    const ACTION_PARSE_FORMAT = '/^(\w+)\[(\w+), (\w+), (.*?)\]$/';

    private $target;

    private $property;

    private $value;

    public function create($command)
    {
        $this->type = $command->type;
        $this->target = $command->target;
        $this->property = $command->property;
        $this->value = $command->value;

        return $this;
    }

    public function parse($string)
    {
        $this->parseString($string, function ($matches) {
            $this->type = $matches[1];
            $this->target = $matches[2];
            $this->property = $matches[3];
            $this->value = $matches[4];
        });

        return get_object_vars($this);
    }

    public function __toString()
    {
        return sprintf("%s[%s, %s, %s]", $this->type, $this->target, $this->property, $this->value);
    }
}