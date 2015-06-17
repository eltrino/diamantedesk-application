<?php

namespace Diamante\AutomationBundle\Rule\Provider;

use Diamante\AutomationBundle\Rule\Fact\Fact;

interface RuleProvider
{
    public function getWorkflowRules(Fact $fact);
    public function getBusinessRules(Fact $fact);
}