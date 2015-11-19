<?php

namespace Diamante\AutomationBundle\Rule\Action\Entity;

interface Action
{
    public function create($command);

    public function parse($string);
}