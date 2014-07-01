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

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadHandler
{
    private $uploadDir;

    public function __construct(\SplFileInfo $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    /**
     * Upload (move to target dir) given file
     * @param UploadedFile $uploadedFile
     * @param $destRelativePath
     * @return File
     */
    public function upload(UploadedFile $uploadedFile)
    {
        if (false === $this->uploadDir->isDir() || false === $this->uploadDir->isWritable()) {
            throw new \RuntimeException('Upload directory is not writable or does not exist.');
        }

        return $uploadedFile->move($this->uploadDir->getRealPath(), $uploadedFile->getClientOriginalName());
    }

    public static function create($kernelRootDir)
    {
        return new FileUploadHandler(
            new \SplFileInfo(realpath($kernelRootDir . '/'
                . \Eltrino\DiamanteDeskBundle\Attachment\Model\Attachment::ATTACHMENTS_DIRECTORY))
        );
    }
}
