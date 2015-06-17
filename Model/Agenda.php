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

namespace Diamante\AutomationBundle\Model;

use Diamante\AutomationBundle\Rule\Action\Action;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;

class Agenda implements \Diamante\AutomationBundle\Rule\Action\Agenda
{
    /**
     * @var Action[]
     */
    protected $actions = [];

    public function push(Action $action)
    {
        $this->actions[spl_object_hash($action)] = $action;
    }

    public function pop(Action $action)
    {
        $hash = spl_object_hash($action);

        if (!array_key_exists($hash, $this->actions)) {
            throw new \RuntimeException('Trying to remove action that does not exist!');
        }

        unset($this->actions[$hash]);
    }

    public function run(ExecutionContext $context)
    {
        try {
            foreach ($this->actions as $key => $action) {
                $action->run($context);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clear()
    {
        $this->actions = [];
    }

    public function isClean()
    {
        return (bool)count($this->actions);
    }
}