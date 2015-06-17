<?php

namespace Diamante\AutomationBundle\Rule\Engine;

use Diamante\AutomationBundle\Model\Fact;

interface Engine
{
    public function check(Fact $fact);
}