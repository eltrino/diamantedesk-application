<?php
/*
 * Copyright (c) 2015 Eltrino LLC (http://eltrino.com)
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

namespace Diamante\AutomationBundle\Rule\Condition\Expression;

use Diamante\AutomationBundle\EventListener\EventTriggeredListener;
use Diamante\AutomationBundle\Rule\Condition\AbstractCondition;
use Diamante\AutomationBundle\Rule\Fact\AbstractFact;

class Changed extends AbstractCondition
{
    /**
     * @param AbstractFact $fact
     *
     * @return bool
     */
    public function isSatisfiedBy(AbstractFact $fact)
    {
        if (EventTriggeredListener::UPDATED != $fact->getAction()) {
            return false;
        }

        $changeset = $fact->getTargetChangeset();
        list($old, $new) = $changeset[$this->context->getProperty()];
        $propertyHandler = $this->propertyManager->getPropertyHandler($fact->getTargetType());
        $propertyHandler->setContext($this->context);
        $old = $propertyHandler->processPropertyValue($fact->getTargetType(), $old);
        $new = $propertyHandler->processPropertyValue($fact->getTargetType(), $new);

        return $old != $new;
    }
}