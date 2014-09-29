<?php

namespace Diamante\DeskBundle\Tests\Stubs;

use \Symfony\Component\HttpFoundation\File\UploadedFile;
use \Symfony\Component\HttpFoundation\File\File;

class UploadedFileStub extends UploadedFile
{
    private $originalName;
    private $mimeType;

    public function __construct($path, $originalName, $mimeType = null)
    {
        $this->originalName = $originalName;
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
}
