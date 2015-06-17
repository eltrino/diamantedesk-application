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

namespace Diamante\AutomationBundle\Infrastructure\Persistence\Doctrine\DBAL\Types;

use Diamante\AutomationBundle\Model\Target;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TargetType extends StringType
{
    const TARGET_TYPE = 'target';

    public function getName()
    {
        return self::TARGET_TYPE;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return '';
        }
        if (false === ($value instanceof Target)) {
            throw new \RuntimeException("Value should be of Target type.");
        }
        return parent::convertToDatabaseValue($value->getValue(), $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new Target($value);
    }

}