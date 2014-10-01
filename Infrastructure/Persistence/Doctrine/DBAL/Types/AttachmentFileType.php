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

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Diamante\DeskBundle\Model\Attachment\File;

class AttachmentFileType extends StringType
{
    const FILE_TYPE = 'file';

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::FILE_TYPE;
    }

    /**
     * @param string $value
     * @param AbstractPlatform $platform
     * @return File|mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new File($value);
    }

    /**
     * @param File $value
     * @param AbstractPlatform $platform
     * @return string
     * @throws \RuntimeException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return '';
        }
        if (false === ($value instanceof File)) {
            throw new \RuntimeException("Value should be a File type.");
        }
        /** @var $value File */
        return parent::convertToDatabaseValue($value->getPathname(), $platform);
    }
}
