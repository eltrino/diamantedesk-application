<?php

namespace Eltrino\DiamanteDeskbundle\Tests\Stubs;

use \Symfony\Component\HttpFoundation\File\UploadedFile;
use \Symfony\Component\HttpFoundation\File\File;

class TestUploadedFile extends UploadedFile
{
    public function __construct($path, $originalName)
    {
        parent::__construct($path, $originalName, null, null, null, true);
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
        if ($this->isValid()) {
            $target = $this->getTargetFile($directory, $name);
            copy($this->getPathname(), $target);

            return new File($target, false);
        }
    }
}