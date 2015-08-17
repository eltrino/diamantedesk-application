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
namespace Diamante\DeskBundle\Tests\Stubs;

use \Symfony\Component\HttpFoundation\File\UploadedFile;
use \Symfony\Component\HttpFoundation\File\File;

class UploadedFileStub extends UploadedFile
{
    private $originalName;
    private $mimeType;
    private $fileName;

    public function __construct($path, $originalName, $mimeType = null)
    {
        $this->originalName = $originalName;
        $this->fileName = $originalName;
        $this->mimeType = $mimeType ?: 'application/octet-stream';
    }

    /**
     * Override of parent's move method in order to copy, not move the fixture file from directory
     *
     * @param string $directory
     * @param string $name
     * @return File
     */
    public function move($directory, $name = null)
    {
        return new File($directory . '/' . $name, false);
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function __toString()
    {
        return $this->originalName;
    }

    public function getFilename()
    {
        return $this->fileName;
    }

    public function getClientOriginalName()
    {
        return $this->originalName;
    }
}
