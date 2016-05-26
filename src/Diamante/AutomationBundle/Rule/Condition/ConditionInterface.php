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

namespace Diamante\AutomationBundle\Rule\Condition;

use Diamante\AutomationBundle\Rule\Fact\AbstractFact;

interface ConditionInterface
{
    /**
     * @param AbstractFact $fact
     * @return bool
     */
    public function isSatisfiedBy(AbstractFact $fact);

    /**
     * @return array
     */
    public function export();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function __toString();
}