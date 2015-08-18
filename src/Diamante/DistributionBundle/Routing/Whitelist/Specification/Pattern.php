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

namespace Diamante\DistributionBundle\Routing\Whitelist\Specification;


class Pattern extends WhitelistVotingSpecification
{
    public function getType()
    {
        return self::TYPE_PATTERN;
    }

    public function isItemWhitelisted($item, $whitelist)
    {
        foreach ($whitelist as $rule) {
            if (preg_match(sprintf('/%s/', $rule), $item) === 1) {
                return true;
            }
        }

        return false;
    }
}