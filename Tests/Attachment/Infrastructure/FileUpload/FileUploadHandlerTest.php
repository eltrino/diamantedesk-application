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

namespace Eltrino\DiamanteDeskBundle\Tests\Attachment\Infrastructure\FileUpload;

use Eltrino\DiamanteDeskBundle\Attachment\Infrastructure\FileUpload\FileUploadHandler;
use Eltrino\PHPUnit\MockAnnotations\MockAnnotations;
use Symfony\Component\HttpFoundation\File\File;

class FileUploadHandlerTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_REAL_PATH = 'dummy_real_path';

    /**
     * @var FileUploadHandler
     */
    private $fileUploadHandler;

    /**
     * @var \SplFileInfo
     * @Mock \SplFileInfo
     */
    private $fileInfo;

    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     * @Mock \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    private $uploadedFile;

    protected function setUp()
    {
        MockAnnotations::init($this);
        $this->fileUploadHandler = new FileUploadHandler($this->fileInfo);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable or does not exist.
     */
    public function thatFileUploadingThrowsExceptionWhenDirIsNotCreated()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(false));
        $this->fileInfo->expects($this->exactly(0))->method('isWritable')->will($this->returnValue(true));

        $justUploadedFile = $this->fileUploadHandler->upload($this->uploadedFile);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Upload directory is not writable or does not exist.
     */
    public function thatFileUploadingThrowsExceptionWhenDirIsNotWritable()
    {
        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(true));
        $this->fileInfo->expects($this->once())->method('isWritable')->will($this->returnValue(false));

        $justUploadedFile = $this->fileUploadHandler->upload($this->uploadedFile);
    }

    /**
     * @test
     */
    public function thatFileUploads()
    {
        $file = new File('filename.ext', false);

        $this->fileInfo->expects($this->once())->method('isDir')->will($this->returnValue(true));

        $this->fileInfo->expects($this->once())->method('isWritable')->will($this->returnValue(true));

        $this->fileInfo->expects($this->once())->method('getRealPath')->will($this->returnValue(self::DUMMY_REAL_PATH));

        $this->uploadedFile->expects($this->once())->method('move')->with(self::DUMMY_REAL_PATH)
            ->will($this->returnValue($file));

        $justUploadedFile = $this->fileUploadHandler->upload($this->uploadedFile);

        $this->assertSame($file, $justUploadedFile);
    }
}
