<?php
/**
 * Created by PhpStorm.
 * User: Ruslan Voitenko
 * Date: 7/8/14
 * Time: 2:45 PM
 */

namespace Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\Persistence\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Eltrino\DiamanteDeskBundle\Attachment\Model\File;

class FileType extends StringType
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
