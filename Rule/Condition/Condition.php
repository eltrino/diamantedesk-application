<?php

namespace Diamante\AutomationBundle\Rule\Condition;

use Diamante\AutomationBundle\Rule\Fact\Fact;

interface Condition
{
    public function isSatisfiedBy(Fact $fact);
}