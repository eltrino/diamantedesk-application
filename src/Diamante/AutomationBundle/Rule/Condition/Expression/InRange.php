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


use Diamante\AutomationBundle\Rule\Condition\AbstractCondition;
use Diamante\AutomationBundle\Rule\Fact\Fact;

class InRange extends AbstractCondition
{
    const RANGE_LIMITS_INCLUDE = 1;
    const RANGE_LIMITS_EXCLUDE = 0;

    public function __construct($property, $expectedValue)
    {
        $this->property = $property;
        $this->rangeStart = isset($expectedValue['rangeStart']) ? $expectedValue['rangeStart'] : null;
        $this->rangeEnd   = isset($expectedValue['rangeEnd']) ? $expectedValue['rangeEnd'] : null;
        $this->strategy   = isset($expectedValue['strategy']) ? $expectedValue['strategy'] : self::RANGE_LIMITS_INCLUDE;

        $this->ensureRangeBoundariesExist();
    }

    /**
     * @param Fact $fact
     * @return mixed
     */
    public function isSatisfiedBy(Fact $fact)
    {
        $actualValue = $this->extractPropertyValue($fact);

        switch ($this->strategy) {
            case (self::RANGE_LIMITS_EXCLUDE):
                $result = (($actualValue > $this->rangeStart) && ($actualValue < $this->rangeEnd));
                break;
            case (self::RANGE_LIMITS_INCLUDE):
                $result = (($actualValue >= $this->rangeStart) && ($actualValue <= $this->rangeEnd));
                break;
            default:
                throw new \RuntimeException("Invalid configuration for range check");
        }

        return $result;
    }

    private function ensureRangeBoundariesExist()
    {
        if (is_null($this->rangeStart) || is_null($this->rangeEnd) || ($this->rangeEnd === $this->rangeStart)) {
            throw new \RuntimeException("Range boundaries are invalid");
        }
    }
}