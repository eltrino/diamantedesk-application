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

namespace Diamante\AutomationBundle\Rule\Action;

class ActionProviderImpl implements ActionProvider
{
    /**
     * @var array
     */
    protected $strategies = [];

    /**
     * @param \Diamante\AutomationBundle\Rule\Action\ActionStrategy $strategy
     */
    public function addStrategy(ActionStrategy $strategy)
    {
        $this->strategies[$strategy->getType()][] = $strategy;
    }

    /**
     * @param \Diamante\AutomationBundle\Rule\Action\ExecutionContext $context
     * @return \Diamante\AutomationBundle\Rule\Action\Action|null
     * @throws \Exception
     */
    public function getAction(ExecutionContext $context)
    {
        if (!array_key_exists($context->getActionType(), $this->strategies)) {
            throw new \Exception(sprintf("Unknown action type configured: %s", $context->getActionType()));
        }

        $strategies = $this->strategies[$context->getActionType()];

        /** @var ActionStrategy $strategy */
        foreach ($strategies as $strategy) {
            if ($strategy->isApplicable($context)) {
                return new Action($strategy);
            }
        }

        return null;
    }
}