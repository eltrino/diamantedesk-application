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
 
/**
 * Created by PhpStorm.
 * User: s3nt1nel
 * Date: 19/11/14
 * Time: 8:23 PM
 */

namespace Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\DBAL\Types;

use Diamante\DeskBundle\Model\User\User;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TicketUserType extends StringType
{
    const USER_TYPE = 'user_type';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::USER_TYPE;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if(empty($value)) {
            return null;
        }
        list($type, $id) = explode(User::DELIMITER, $value);

        return new User($id, $type);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return '';
        }
        if (false === ($value instanceof User)) {
            throw new \RuntimeException("Value should be a User type.");
        }

        return parent::convertToDatabaseValue((string)$value, $platform);
    }
} 