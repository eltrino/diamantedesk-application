<?php


namespace Diamante\AutomationBundle\Rule\Action;


interface ActionProvider
{
    /**
     * @param \Diamante\AutomationBundle\Rule\Action\ActionStrategy $strategy
     */
    public function addStrategy(ActionStrategy $strategy);

    /**
     * @param \Diamante\AutomationBundle\Rule\Action\ExecutionContext $context
     * @return Action
     */
    public function getAction(ExecutionContext $context);
}