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


class CronExpressionMapper
{
     protected static $expressionMap = [
        '5m'    => '*/5 * * * *',
        '10m'   => '*/10 * * * *',
        '15m'   => '*/15 * * * *',
        '20m'   => '*/20 * * * *',
        '30m'   => '*/30 * * * *',
        '1h'    => '0 */1 * * *',
        '2h'    => '0 */2 * * *',
        '4h'    => '0 */4 * * *',
        '8h'    => '0 */8 * * *',
        '12h'   => '0 */12 * * *',
        '24h'   => '0 0 * * *',
        '3d'    => '0 0 */3 * *',
        '7d'    => '0 0 */7 * *',
        '25d'   => '0 0 */25 * *',
        '30d'   => '0 0 */30 * *'
    ];

    public static function getMappedCronExpression($humanReadableExpression)
    {
        if (!in_array($humanReadableExpression, array_keys(static::$expressionMap))) {
            throw new \RuntimeException(sprintf("Expression %s is incorrect configuration value.", $humanReadableExpression));
        }

        return static::$expressionMap[$humanReadableExpression];
    }

    public static function getFrontendOptionsConfig()
    {
        return [
            'm' => [5,10,15,20,30],
            'h' => [1,2,4,8,12,24],
            'd' => [3,7,25,30]
        ];
    }

    public static function getConfiguredTimeIntervals()
    {
        return array_keys(static::$expressionMap);
    }
}