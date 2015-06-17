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

namespace Diamante\AutomationBundle\Rule\Condition\Specific;

use Diamante\AutomationBundle\Model\Target;
use Diamante\AutomationBundle\Rule\Condition\AbstractCondition;
use Diamante\AutomationBundle\Rule\Fact\Fact;

class HasComments extends AbstractCondition
{
    public function __construct()
    {
    }

    public function isSatisfiedBy(Fact $fact)
    {
        if ($fact->getTargetType() !== Target::TARGET_TYPE_TICKET) {
            return false;
        }

        $comments = $fact->getTarget()->getComments();

        return (bool)count($comments);
    }
}