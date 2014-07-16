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

namespace Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileUpload;

use Eltrino\DiamanteDeskBundle\Attachment\Api\Dto\FileDto;
use Eltrino\DiamanteDeskBundle\Attachment\Model\File;
use Symfony\Component\Filesystem\Filesystem;

class FileUploadHandler
{
    /**
     * @var Filesystem
     */
    private $fs;

    private $uploadDir;

    public function __construct(\SplFileInfo $uploadDir, Filesystem $fs)
    {
        $this->uploadDir = $uploadDir;
        $this->fs = $fs;
    }

    /**
     * Upload (move to target dir) given file
     * @param string $filename
     * @param string $content
     * @return File
     * @throws \RuntimeException
     */
    public function upload($filename, $content)
    {
        if (false === $this->uploadDir->isDir() || false === $this->uploadDir->isWritable()) {
            throw new \RuntimeException("Upload directory is not writable, doesn't exist or no space left on the disk.");
        }
        $this->fs->dumpFile($this->uploadDir->getRealPath() . '/' . $filename, $content);
        return new File($this->uploadDir->getRealPath() . '/' . $filename);
    }

    public static function create($kernelRootDir)
    {
        return new FileUploadHandler(
            new \SplFileInfo(realpath($kernelRootDir . '/'
                . \Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment::ATTACHMENTS_DIRECTORY)),
            new Filesystem()
        );
    }
}
