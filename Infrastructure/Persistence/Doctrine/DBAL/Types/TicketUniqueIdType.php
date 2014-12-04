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
namespace Diamante\DeskBundle\Infrastructure\Persistence\Doctrine\DBAL\Types;

use Diamante\DeskBundle\Model\Ticket\UniqueId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class TicketUniqueIdType extends StringType
{
    const TICKET_UNIQUE_ID_TYPE = 'ticket_unique_id';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::TICKET_UNIQUE_ID_TYPE;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new UniqueId((string) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return '';
        }
        if (false === ($value instanceof UniqueId)) {
            throw new \RuntimeException("Value should be a Ticket Unique Id type.");
        }if (!$value) {
            return '';
        }
        if (false === ($value instanceof UniqueId)) {
            throw new \RuntimeException("Value should be a Ticket Unique Id type.");
        }
        return parent::convertToDatabaseValue($value->getValue(), $platform);
    }
}
