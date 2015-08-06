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

namespace Diamante\AutomationBundle\Action\Strategy;

use Diamante\AutomationBundle\Rule\Action\ActionStrategy;
use Diamante\AutomationBundle\Rule\Action\ExecutionContext;

class UpdatePropertyStrategy implements ActionStrategy
{
    const TYPE    = 'UpdateProperty';

    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @param ExecutionContext $context
     * @return bool
     */
    public function isApplicable(ExecutionContext $context)
    {
        return self::TYPE === $context->getActionType();
    }

    /**
     * @param ExecutionContext $context
     */
    public function execute(ExecutionContext $context)
    {
        $target = $context->getTarget();
        $target->updateProperties((array)$context->getActionArguments());
    }
}