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

namespace Diamante\AutomationBundle\Infrastructure\Shared;

class ExpressionValidator
{
    private static $timeIntervals = [
        '5m' => '*/5 * * * *',
        '10m' => '*/10 * * * *',
        '15m' => '*/15 * * * *',
        '20m' => '*/20 * * * *',
        '30m' => '*/30 * * * *',
        '1h' => '0 */1 * * *',
        '2h' => '0 */2 * * *',
        '4h' => '0 */4 * * *',
        '8h' => '0 */8 * * *',
        '12h' => '0 */12 * * *',
        '24h' => '0 0 * * *'
    ];

    public static function validate($value)
    {
        if (!array_key_exists($value, self::$timeIntervals)) {
            throw new \RuntimeException('Incorrect time interval.');
        }

        return self::$timeIntervals[$value];
    }
}