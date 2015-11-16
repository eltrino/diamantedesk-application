<?php

namespace Diamante\AutomationBundle\Rule\Provider;

use Diamante\AutomationBundle\Rule\Fact\Fact;

interface ConditionProvider
{
    public function getWorkflowConditions(Fact $fact);
    public function getBusinessConditions(Fact $fact);
}