<?php


namespace Diamante\AutomationBundle\Rule\Fact;


interface Fact
{
    public function getTarget();
    public function getTargetType();
    public function getTargetChangeset();
}