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
class NotifyByEmail extends AbstractAction
{
    const ACTION_PARSE_FORMAT = '/^(\w+)\[(.*?)\]$/';

    private $notification = 'email';

    private $addressee;

    public function create($command)
    {
        $this->type = $command->type;
        $this->addressee = $command->addressee;

        return $this;
    }

    public function parse($string)
    {
        $this->parseString($string, function ($matches) {
            $this->type = $matches[1];
            $this->addressee = $matches[2];
        });

        return get_object_vars($this);
    }

    public function __toString()
    {
        return sprintf("%s[%s]", $this->type, $this->addressee);
    }
}