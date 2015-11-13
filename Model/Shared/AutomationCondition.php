<?php

namespace Diamante\AutomationBundle\Model\Shared;

use Diamante\AutomationBundle\Rule\Fact\Fact;

interface AutomationCondition
{
    public function isSatisfiedBy(Fact $fact);
    public function activate();
    public function deactivate();
    public function hasChildren();
}