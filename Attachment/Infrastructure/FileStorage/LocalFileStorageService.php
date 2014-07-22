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
namespace Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileStorage;

use Eltrino\DiamanteDeskBundle\Attachment\Model\File;
use Eltrino\DiamanteDeskBundle\Attachment\Model\Services\FileStorageService;
use Symfony\Component\Filesystem\Filesystem;

class LocalFileStorageService implements FileStorageService
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var \SplFileInfo
     */
    private $uploadDir;

    public function __construct(\SplFileInfo $uploadDir, Filesystem $fs)
    {
        $this->uploadDir = $uploadDir;
        $this->fs = $fs;
    }

    /**
     * @return string path to filename
     */
    public function upload($filename, $content)
    {
        if (false === $this->uploadDir->isDir()) {
            try {
                $this->fs->mkdir($this->uploadDir->getPathname());
            } catch (\Exception $e) {
                throw new \RuntimeException('Upload directory can not be created.');
            }
        }

        if (false === $this->uploadDir->isWritable()) {
            throw new \RuntimeException('Upload directory is not writable.');
        }

        $this->fs->dumpFile($this->uploadDir->getRealPath() . '/' . $filename, $content);
        return $this->uploadDir->getRealPath() . '/' . $filename;
    }

    public function remove($filename)
    {
        if (empty($filename)) {
            throw new \LogicException('File name can not be empty string.');
        }
        $this->fs->remove($this->uploadDir->getRealPath() . '/' . $filename);
    }

    public static function create($kernelRootDir, $attachmentsDirectoryName)
    {
        return new LocalFileStorageService(
            new \SplFileInfo(realpath($kernelRootDir) . '/' . $attachmentsDirectoryName),
            new Filesystem()
        );
    }
}
